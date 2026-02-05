<?php
namespace Core;

/*
Maybe sinnvoll --> sonst kann man nicht schon mit JSON arbeiten, finde ich
*/

use Exception;
use Imagick;

class ImageList {
    private array $images = [];

    public function __construct(string | null $JSON = null) {
        if ($JSON !== null) {
            $this->createFromJSON($JSON);
        }
    }

    public function append(Image $img) {
        $this->images[] = $img;
    }
    public function get(): array {
        return $this->images;
    }

    /*
     Konvertiert die Images zu einem JSON-Array-String
     Jedes Image wird mit seinen Properties konvertiert:
     - filename: Der Dateiname
     - filepath: Der vollständige Pfad
     - mimetype: Der MIME-Type
     - filesize: Die Dateigröße
     */
    public function toJSON(): string {
        $imagesData = [];
        foreach ($this->images as $img) {
            $imagesData[] = [
                'UUID' => $img->UUID,
                'filepath' => $img->filepath,
                'mimetype' => $img->mimetype,
                'filesize' => $img->filesize,
            ];
        }
        return json_encode($imagesData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function createFromJSON(string $JSON): void {
        $imagesData = json_decode($JSON, true);
        if (!is_array($imagesData)) {
            throw new Exception('Ungültiges JSON-Format für Images');
        }
        
        $this->images = [];
        foreach ($imagesData as $data) {
            $this->images[] = Image::fromJSON($data);
        }
    }

    public function getPaths(): array {
        $paths = [];
        foreach($this->images as $img) {
            $paths[] = $img->filepath;
        }
        return $paths;
    }

    public function convertImgs($compression = 0): bool {
        $newImages = [];

        foreach ($this->images as $img) {
            $image = new Imagick($img->filepath);
            $image->setImageFormat('tif');
            $image->setCompressionQuality($compression);

            $tifPath = $img->folderpath . $img->UUID . '.tif';

            try {
                $image->writeImage($tifPath);

                $image->clear();
                $image->destroy();

                $tifFilesize = filesize($tifPath);

                $tifImg = new Image('image/tif', $tifFilesize, $img->folderpath);
                $tifImg->UUID = $img->UUID;
                $tifImg->filepath = $tifPath;

                $newImages[] = $tifImg;

                if (file_exists($img->filepath)) {
                    unlink($img->filepath);
                }

            } catch (Exception $e) {
                echo 'Fehler: ' . $e->getMessage();
                return false;
            }
        }
        $this->images = $newImages;

        return true;
    }
}