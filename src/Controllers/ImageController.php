<?php
namespace Controllers;
//Ob das zu den Controllern gehört, weiß ich nicht. Fand es aber am logischsten

require __DIR__ . '/../../vendor/autoload.php';

use Core\Image;

class ImageController {
    public array $images = [];
    private array $conf;
    public string $folderpath;

    public function __construct(int $userId) {
        $this->conf = require __DIR__ . '/../../config/config.php';
        $this->folderpath = $this->conf['uploaded_img_path'] . $userId . '/';
    }

    public function uploadImgs(array $files, int $userId): string{
        // Unterstütze verschiedene Datei-Strukturen
        // Struktur 1: $_FILES['image'] mit mehreren Dateien (image[])
        if (isset($files['image']) && is_array($files['image']['name'])) {
            $fileCount = count($files['image']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['image']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue; // Überspringe Dateien mit Fehlern
                }
                
                $img = new Image(
                    $files['image']['type'][$i],
                    $files['image']['size'][$i],
                    $this->folderpath
                );

                if($img->isValidImg()){
                    // Datei speichern
                    if($img->saveImg($files['image']['tmp_name'][$i])){
                        $this->images[] = $img;
                    } else {
                        throw new \Exception("Fehler beim Speichern der Datei: " . $files['image']['name'][$i]);
                    }
                } else {
                    throw new \Exception("Unerlaubter Dateityp: " . $files['image']['name'][$i]);
                }
            }
        }
        // Struktur 2: Einzelne Datei oder andere Struktur
        else {
            foreach($files as $key => $file) {
                // Überspringe, wenn es ein Array mit mehreren Dateien ist (wurde oben behandelt)
                if (is_array($file) && isset($file['name']) && is_array($file['name'])) {
                    continue;
                }
                
                // Einzelne Datei verarbeiten
                if (isset($file['type']) && isset($file['size']) && isset($file['tmp_name'])) {
                    if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
                        continue; // Überspringe Dateien mit Fehlern
                    }
                    
                    $img = new Image($file['type'], $file['size'], $this->folderpath);

                    if($img->isValidImg()){
                        // Datei speichern
                        if($img->saveImg($file['tmp_name'])){
                            $this->images[] = $img;
                        } else {
                            throw new \Exception("Fehler beim Speichern der Datei");
                        }
                    } else {
                        throw new \Exception("Unerlaubter Dateityp");
                    }
                }
            }
        }
        return $this->folderpath;
    }

    public function getImgsByFundId(int $fundId){

    }
}