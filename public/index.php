<?php

require_once __DIR__ . '/../src/Core/Request.php';
require_once __DIR__ . '/../src/Core/Response.php';

use Core\Request;
use Core\Response;

$request = new Request();

switch ($request->path()) {
    default:
        Response::json(['error' => 'Not found'], 404);
}
