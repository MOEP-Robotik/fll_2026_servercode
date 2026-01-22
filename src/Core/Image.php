<?php
namespace Core;

require __DIR__ . '/../../vendor/autoload.php';

class Image {
    public string $filepath;
    public string $filename;
    public string $mimetype;
    public int $filesize;
    private array $conf;

    public function __construct(
        string $mimetype,
        int $filesize,
        int $userId
    ) {
        $this->conf = require __DIR__ . '/../../config/config.php';
        $this->filename = guidv4();
        $this->filepath = $this->conf['uploaded_img_path'] . $userId . '/' . $this->filename;
        $this->mimetype = $mimetype;
        $this->filesize = $filesize;
    }

    public function isValidImg(): bool {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/tif'];
        return in_array($this->mimetype, $allowed_types);
    }
}