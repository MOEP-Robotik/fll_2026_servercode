<?php
require __DIR__ . '/../vendor/autoload.php';

use Core\Request;
use Core\Response;
use Controllers\SubmissionController;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    Response::handleOptionsRequest();
}

$request = new Request();

switch ($request->path()) {
    case "/api/health":
        Response::json(['health' => 'ok']);
        break;
    default:
        if (str_starts_with($request->path(), "/api/submissions")) {
            $controller = new SubmissionController();
            $controller->submit($request);
            break;
        }
        Response::json(['error' => 'Not found'], 404);
}
