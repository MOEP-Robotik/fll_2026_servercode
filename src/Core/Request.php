<?php
namespace Core;

class Request {
    public function method(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function path(): string {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function json(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
