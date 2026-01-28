<?php
namespace Core;

require __DIR__ . '/../../vendor/autoload.php';

$imgs = new Imagelist("[{\"filename\":\"e4c3034f-4481-40f5-81b3-f0c24d8285a0.jpg\",\"filepath\":\"\/home\/fgier\/Documents\/FLL\/backend\/config\/..\/..\/uploads\/1\/e4c3034f-4481-40f5-81b3-f0c24d8285a0.jpg\",\"mimetype\":\"image\/jpeg\",\"filesize\":38229},{\"filename\":\"1f9030f3-02f0-4b87-8d63-25074b0cc4b7.jpg\",\"filepath\":\"\/home\/fgier\/Documents\/FLL\/backend\/config\/..\/..\/uploads\/1\/1f9030f3-02f0-4b87-8d63-25074b0cc4b7.jpg\",\"mimetype\":\"image\/jpeg\",\"filesize\":48568}]");

echo($imgs->getPaths());