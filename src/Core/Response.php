<?php
namespace Core;

class Response {
    public static function json($data, $status = 200, $success = true): void
    {
        if ($status != 200)
            $success = false;

        self::setCorsHeaders();

        header('Content-Type: application/json');
        http_response_code($status);
        $dataDump = var_export($data, true);
        error_log("response ($status): $dataDump");
        echo json_encode([
            "success" => $success,
            "status" => $status,
            "data" => $data
        ]);
    }

    public static function setCorsHeaders(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400'); // 24 hours
    }

    public static function handleOptionsRequest(): void
    {
        self::setCorsHeaders();
        http_response_code(200);
        exit;
    }
}
