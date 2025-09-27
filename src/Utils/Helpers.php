<?php

namespace App\Utils;

class Helpers {
    public static function formatResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function logError($message) {
        $logFile = __DIR__ . '/../../logs/app.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: " . $message . PHP_EOL, FILE_APPEND);
    }

    public static function validateInput($input, $rules) {
        foreach ($rules as $field => $rule) {
            if (!isset($input[$field]) || empty($input[$field])) {
                self::logError("Validation failed for field: $field");
                return false;
            }
        }
        return true;
    }
}