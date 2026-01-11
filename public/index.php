<?php

require_once __DIR__ . '/../src/Core/Request.php';
require_once __DIR__ . '/../src/Core/Response.php';

use Core\Request;
use Core\Response;

$request = new Request();

switch ($request->path()) {
    case "/api/health":
        Response::json(['health' => 'ok']);
        break;
    default:
        if (str_starts_with($request->path(), "/api/submissions")) {
            require_once __DIR__ . '/../src/Controllers/SubmissionController.php';
            $controller = new SubmissionController();
            $controller->submit($request);
            break;
        }
        Response::json(['error' => 'Not found'], 404);
}
