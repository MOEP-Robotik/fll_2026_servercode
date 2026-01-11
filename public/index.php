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
    case '/api/submissions':
        require_once __DIR__ . '/../src/Controllers/SubmissionController.php';
        (new SubmissionController())->submit($request);
        break;
    default:
        Response::json(['error' => 'Not found'], 404);
}
