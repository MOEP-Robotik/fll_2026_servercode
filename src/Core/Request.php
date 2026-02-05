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

    public function formData(): array {
        $result = [];
        foreach ($_POST as $key => $value) {
            $this->setNestedValue($result, $key, $value);
        }
        return $result;
    }

    private function setNestedValue(array &$array, string $key, mixed $value): void {
        if (preg_match('/^([^\[]+)\[([^\]]*)\](.*)$/', $key, $matches)) {
            $baseKey = $matches[1];
            $subKey = $matches[2];
            $remaining = $matches[3];
            
            if (!isset($array[$baseKey])) {
                $array[$baseKey] = [];
            }
            
            if ($remaining !== '') {
                $this->setNestedValue($array[$baseKey], $subKey . $remaining, $value);
            } else if ($subKey === '') {
                $array[$baseKey][] = $value;
            } else {
                $array[$baseKey][$subKey] = $value;
            }
        } else {
            $array[$key] = $value;
        }
    }

    public function files(): array {
        return $_FILES ?? [];
    }

    public function header(): array {
        return getallheaders();
    }
}
