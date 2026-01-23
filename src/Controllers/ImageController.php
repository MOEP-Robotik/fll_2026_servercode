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
        foreach($files as $file) {
            $img = new Image($file['images']['type'], $file['images']['size'], $this->folderpath);

            if($img->isValidImg()){
                // Datei speichern
                if($img->saveImg($file['images']['tmp_name'])){
                    $this->images[] = $img;
                } else {
                    throw new \Exception("Fehler beim Speichern der Datei");
                }
            } else {
                throw new \Exception("Unerlaubter Dateityp");
            }
        }
        return $this->folderpath;
    }

    public function getImgsByFundId(int $fundId){

    }
}