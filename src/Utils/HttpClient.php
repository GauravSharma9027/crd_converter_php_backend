<?php

namespace App\Utils;

class HttpClient {
    public function get($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('HTTP error: ' . $httpCode);
        }

        return json_decode($response, true);
    }
}