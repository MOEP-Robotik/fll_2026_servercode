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
        $this->filename = UUID::guidv4() . $extension;
        $this->filepath = $folderpath . $this->filename;
        $this->mimetype = $mimetype;
        $this->filesize = $filesize;
        $this->folderpath = $folderpath;
    }

    private function getExtensionFromMimeType(string $mimetype): string {
        $mimeToExt = [
            'image/jpeg' => '.jpg',
            'image/jpg' => '.jpg',
            'image/jfif' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp',
            'image/tif' => '.tif',
            'image/tiff' => '.tif',
        ];
        return $mimeToExt[$mimetype] ?? '';
    }

    public function isValidImg(): bool {
        $allowed_types = ['image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/tif', 'image/jpeg', 'image/jfif', 'image/tiff'];
        return in_array($this->mimetype, $allowed_types);
    }

    public static function fromJSON(array $data): Image {
        // Erstelle ein Dummy-Image und überschreibe dann die Werte
        $image = new self($data['mimetype'], $data['filesize'], dirname($data['filepath']) . '/');
        $image->filename = $data['filename'];
        $image->filepath = $data['filepath'];
        return $image;
    }

    public function saveImg(string $tempFilePath): bool {
        // Verzeichnis erstellen, falls nicht vorhanden
        if (!is_dir($this->folderpath)) {
            if (!mkdir($this->folderpath, 0755, true)) {
                $error = error_get_last();
                throw new \Exception(
                    "Konnte Verzeichnis nicht erstellen: {$this->folderpath}. " .
                    "Fehler: " . ($error['message'] ?? 'Unbekannter Fehler') .
                    " Bitte stellen Sie sicher, dass PHP Schreibrechte für das übergeordnete Verzeichnis hat."
                );
            }
        }
        
        // Prüfen, ob das Verzeichnis jetzt existiert und beschreibbar ist
        if (!is_dir($this->folderpath) || !is_writable($this->folderpath)) {
            throw new \Exception(
                "Verzeichnis ist nicht beschreibbar: {$this->folderpath}. " .
                "Bitte stellen Sie sicher, dass PHP Schreibrechte für dieses Verzeichnis hat."
            );
        }
        
        // Datei verschieben von temporärem Speicherort zum Zielort
        if (!move_uploaded_file($tempFilePath, $this->filepath)) {
            $error = error_get_last();
            throw new \Exception(
                "Konnte Datei nicht speichern: {$this->filepath}. " .
                "Fehler: " . ($error['message'] ?? 'Unbekannter Fehler')
            );
        }
        
        return true;
    }
}