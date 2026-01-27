<?php
namespace Controllers;
//Ob das zu den Controllern gehört, weiß ich nicht. Fand es aber am logischsten

require __DIR__ . '/../../vendor/autoload.php';

use Core\Image;
use Core\Imagelist;

class ImageController {
    public Imagelist $images;
    private array $conf;
    public string $folderpath;

    public function __construct(int $userId) {
        $this->conf = require __DIR__ . '/../../config/config.php';
        $this->folderpath = $this->conf['uploaded_img_path'] . $userId . '/';
        $this->images = new Imagelist();
    }

    public function uploadImgs(array $files, int $userId): string{
        //Struktur: $_FILES['images]...
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
                        $this->images->append($img);
                    } else {
                        throw new \Exception("Fehler beim Speichern der Datei: " . $files['image']['name'][$i]);
                    }
                } else {
                    throw new \Exception("Unerlaubter Dateityp: " . $files['image']['name'][$i]);
                }
            }
        }
        return $this->folderpath;
    }

    public function getImgsByFundId(int $fundId){

    }
}