<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;
use Core\Image;
use Core\Request;
use Core\Response;

class ImageController {
    private array $images;
    private array $conf;

    public function __construct() {
        $this->conf = require __DIR__ . '/../../config/config.php';
    }

    private function uploadImgs(array $files, int $userId): string{
        foreach($files as $file) {
            $img = new Image($file['userfile']['type'], $file['userfile']['size'], $userId);

            if($img->isValidImg()){
                $this->images[] = $img;
            } else {
                throw new \Exception("Unerlaubter Dateityp");
            }
        }
        return $this->conf['uploaded_img_path'] . $userId . '/';
    }

    private function getImgs(int $userId){

    }
}