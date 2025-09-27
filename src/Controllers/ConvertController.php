<?php
namespace App\Controllers;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConvertController
{
    private array $supportedOutputs = ["pdf", "svg", "png", "tiff", "jpg", "jpeg", "ps"];

    public function convertFile(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        $outputFormat = strtolower($request->getParsedBody()['output_format'] ?? 'pdf');

        if (!in_array($outputFormat, $this->supportedOutputs)) {
            $payload = ['error' => "Output format {$outputFormat} not supported."];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Save uploaded file
        $file = $uploadedFiles['file'] ?? null;
        if (!$file) {
            $response->getBody()->write(json_encode(['error' => 'No file uploaded']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $uploadPath = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

        $filePath = $uploadPath . time() . "_" . $file->getClientFilename();
        $file->moveTo($filePath);

        try {
            $client = new Client();

            // Step 1: Create Job
            $tasks = $this->buildTasks($outputFormat);
            $jobResponse = $client->post("https://api.cloudconvert.com/v2/jobs", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['CLOUDCONVERT_API_KEY'],
                    'Content-Type'  => 'application/json'
                ],
                'json' => ['tasks' => $tasks]
            ]);

            $job = json_decode($jobResponse->getBody()->getContents(), true)['data'];

            // Step 2: Upload File
            $uploadTask = array_values(array_filter($job['tasks'], fn($t) => $t['name'] === 'upload-cdr'))[0];
            if (!isset($uploadTask['result']['form'])) throw new \Exception("Upload task form missing");

            $form = $uploadTask['result']['form'];
            $multipart = [];
            foreach ($form['parameters'] as $k => $v) {
                $multipart[] = ['name' => $k, 'contents' => $v];
            }
            $multipart[] = ['name' => 'file', 'contents' => fopen($filePath, 'r')];

            $client->post($form['url'], ['multipart' => $multipart]);
            unlink($filePath); // delete local file

            // Step 3: Poll for export result
            $exportTask = array_values(array_filter($job['tasks'], fn($t) => $t['name'] === 'export-file'))[0];
            $fileUrl = null;

            for ($i = 0; $i < 15; $i++) {
                $taskRes = $client->get("https://api.cloudconvert.com/v2/tasks/" . $exportTask['id'], [
                    'headers' => ['Authorization' => 'Bearer ' . $_ENV['CLOUDCONVERT_API_KEY']]
                ]);

                $task = json_decode($taskRes->getBody()->getContents(), true)['data'];

                if ($task['status'] === 'finished' && isset($task['result']['files'][0]['url'])) {
                    $fileUrl = $task['result']['files'][0]['url'];
                    break;
                }
                sleep(3);
            }

            if (!$fileUrl) throw new \Exception("Conversion timeout");

            $payload = ['success' => true, 'downloadUrl' => $fileUrl];
            $response->getBody()->write(json_encode($payload));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $payload = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    private function buildTasks(string $format): array
    {
        if (in_array($format, ["pdf", "svg", "png"])) {
            return [
                "upload-cdr" => ["operation" => "import/upload"],
                "convert-to-target" => [
                    "operation" => "convert",
                    "input" => "upload-cdr",
                    "input_format" => "cdr",
                    "output_format" => $format,
                    "engine" => "inkscape",
                    "filename" => "converted.$format"
                ],
                "export-file" => ["operation" => "export/url", "input" => "convert-to-target"]
            ];
        }

        // Two-step conversion
        return [
            "upload-cdr" => ["operation" => "import/upload"],
            "convert-to-pdf" => [
                "operation" => "convert",
                "input" => "upload-cdr",
                "input_format" => "cdr",
                "output_format" => "pdf",
                "engine" => "inkscape",
                "filename" => "intermediate.pdf"
            ],
            "convert-to-target" => [
                "operation" => "convert",
                "input" => "convert-to-pdf",
                "input_format" => "pdf",
                "output_format" => $format,
                "engine" => "imagemagick",
                "filename" => "converted.$format"
            ],
            "export-file" => ["operation" => "export/url", "input" => "convert-to-target"]
        ];
    }
}
