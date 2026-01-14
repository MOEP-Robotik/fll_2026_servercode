<?php
require __DIR__ . '/../vendor/autoload.php';

use Core\Request;
use Core\Response;
use Controllers\SubmissionController;
use Controllers\AuthController;

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
        } elseif (str_starts_with($request->path(), "/api/login")) {
            $controller = new AuthController();
            //$controller->login_request(parameter, die gesendet werden)
        }
        Response::json(['error' => 'Not found'], 404);
}
