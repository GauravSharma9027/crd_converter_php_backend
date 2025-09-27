<?php
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use App\Middleware\ErrorHandler;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

$app = AppFactory::create();

// ✅ Register Error Handler
ErrorHandler::register($app);

// // ✅ CORS Middleware
// $app->add(function ($request, $handler) {
//     $response = $handler->handle($request);
//     return $response
//         ->withHeader('Access-Control-Allow-Origin', $_ENV['FRONTEND_URL'] ?? '*')
//         ->withHeader('Access-Control-Allow-Credentials', 'true')
//         ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
//         ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
// });

// ✅ CORS Middleware
$app->add(function ($request, $handler) {
    $allowedOrigins = [
        $_ENV['FRONTEND_URL'] ?? '',
        $_ENV['FRONTEND_URL_VERCEL'] ?? ''
    ];

    $origin = $request->getHeaderLine('Origin');
    $response = $handler->handle($request);

    if (in_array($origin, $allowedOrigins)) {
        $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
    }

    return $response
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


// ✅ Static folder like `/converted`
$app->get('/converted/{file}', function ($request, $response, $args) {
    $file = __DIR__ . '/../converted/' . basename($args['file']);
    if (!file_exists($file)) {
        $response->getBody()->write(json_encode([
            "success" => false,
            "message" => "File not found"
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $response->getBody()->write(file_get_contents($file));
    return $response->withHeader('Content-Type', mime_content_type($file));
});

// ✅ Routes include (now fixed)
(require __DIR__ . '/../src/Routes/routes.php')($app);

// ✅ Run App
$app->run();
