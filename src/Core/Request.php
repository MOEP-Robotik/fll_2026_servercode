<?php
namespace Core;

class Request {
    public function method(): string {
        return $_SERVER['REQUEST_METHOD'];
    }
    public function post(): bool {
        return $this->method() == "POST";
    }
    public function get(): bool {
        return $this->method() == "GET";
    }

    public function path(): string {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function json(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    public function postData(): array {
        return $_POST ?? [];
    }

    public function files(): array {
        return $_FILES ?? [];
    }
}
