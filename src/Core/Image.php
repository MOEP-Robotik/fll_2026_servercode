<?php
namespace Core;

require __DIR__ . '/../../vendor/autoload.php';

class Image {
    public string $folderpath;
    public string $filepath;
    public string $filename;
    public string $mimetype;
    public int $filesize;

    public function __construct(
        string $mimetype,
        int $filesize,
        string $folderpath
    ) {
        // Dateiendung basierend auf MIME-Type hinzufügen
        $extension = $this->getExtensionFromMimeType($mimetype);
        $this->filename = \Core\guidv4() . $extension;
        $this->filepath = $folderpath . $this->filename;
        $this->mimetype = $mimetype;
        $this->filesize = $filesize;
        $this->folderpath = $folderpath;
    }

    private function getExtensionFromMimeType(string $mimetype): string {
        $mimeToExt = [
            'image/jpeg' => '.jpg',
            'image/jpg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp',
            'image/tif' => '.tif',
            'image/tiff' => '.tif',
        ];
        return $mimeToExt[$mimetype] ?? '';
    }

    public function isValidImg(): bool {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/tif'];
        return in_array($this->mimetype, $allowed_types);
    }

    public function saveImg(string $tempFilePath): bool {
        // Verzeichnis erstellen, falls nicht vorhanden
        if (!is_dir($this->folderpath)) {
            mkdir($this->folderpath, 0755, true);
        }
        
        // Datei verschieben von temporärem Speicherort zum Zielort
        if (move_uploaded_file($tempFilePath, $this->filepath)) {
            return true;
        }
        
        return false;
    }
}