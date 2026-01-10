<?php
namespace Core;

class Response {
    public static function json($data, $status = 200, $success = true): void
    {
        if ($status != 200)
            $success = false;

        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode([
            "success" => $success,
            "status" => $status,
            "data" => $data
        ]);
    }
}
