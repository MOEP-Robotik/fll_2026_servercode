<?php
namespace Core;

/*
Maybe sinnvoll --> sonst kann man nicht schon mit JSON arbeiten, finde ich
*/

use Exception;
use Imagick;

class ImageList {
    private array $images;

    public function __construct(string | null $JSON = null) {
        $this->images = [];
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

    private function createFromJSON(string | null $JSON): void {
        //Es besteht die Möglichkeit, dass das ganz komisch encoded wird, weshalb dieses doppelte decoden nötig ist... Grade keine Zeit den Fehler zu suchen
        if ($JSON === null || $JSON === '' || $JSON === '[]') {
            $this->images = [];
            return;
        }
        
        $imagesData = json_decode($JSON, true);
        
        if (is_string($imagesData)) {
            $imagesData = json_decode($imagesData, true);
        }
        
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

    public function convertImgs($compression = 100): bool {
        $newImages = [];

        foreach ($this->images as $img) {
            $image = new Imagick($img->filepath);
            $image->setImageFormat('jpeg');
            $image->setCompressionQuality($compression);

            $jpgpath = $img->folderpath . $img->UUID . '.jpg';

            try {
                $image->writeImage($jpgpath);

                $image->clear();
                $image->destroy();

                $jpgFilesize = filesize($jpgpath);

                $jpgImg = Image::fromJSON([
                    'UUID' => $img->UUID,
                    'filepath' => $jpgpath,
                    'mimetype' => 'image/jpeg',
                    'filesize' => $jpgFilesize,
                ]);

                $newImages[] = $jpgImg;

                if (file_exists($img->filepath)) {
                    unlink($img->filepath);
                }

            } catch (Exception $e) {
                $image->clear();
                $image->destroy();
                
                error_log(
                    'Fehler bei der Bildkonvertierung für "' . $img->filepath . '": ' . $e->getMessage()
                );
                return false;
            }
        }
        $this->images = $newImages;

        return true;
    }
}