<?php
namespace Controllers;
//Ob das zu den Controllern gehört, weiß ich nicht. Fand es aber am logischsten

require __DIR__ . '/../../vendor/autoload.php';

use Core\Image;
use Core\Imagelist;
use Database\SubmissionDatabase;

class ImageController {
    public Imagelist $images;
    private array $conf;
    public string $folderpath;

    public function __construct(int $userId) {
        $this->conf = require __DIR__ . '/../../config/config.php';
        $this->folderpath = $this->conf['uploaded_img_path'] . $userId . '/';
        $this->images = new Imagelist();
    }

    public function uploadImgs(array $files): string{
        //Struktur: $_FILES['images]...
        if (isset($files['images']) && is_array($files['images']['name'])) {
            $fileCount = count($files['images']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['images']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue; // Überspringe Dateien mit Fehlern
                }
                
                $img = new Image(
                    $files['images']['type'][$i],
                    $files['images']['size'][$i],
                    $this->folderpath
                );

                if($img->isValidImg()){
                    // Datei speichern
                    if($img->saveImg($files['images']['tmp_name'][$i])){
                        $this->images->append($img);
                    } else {
                        throw new \Exception("Fehler beim Speichern der Datei: " . $files['images']['name'][$i]);
                    }
                } else {
                    throw new \Exception("Unerlaubter Dateityp: " . $files['images']['name'][$i]);
                }
            }
        }
        return $this->folderpath;
    }

    public function getImgsBySubmissionId(int $submissionId) {
        $repo = new SubmissionDatabase();
        $submiss = $repo->getById($submissionId);
        if (!$submiss) {
            throw new \Exception("Submission existiert nicht");
        }
        $json =  $submiss->files;
        $imglist = new Imagelist($json);
        $this->images = $imglist;
        return $imglist;
    }

    public function convertImages(): bool {
        return $this->images->convertImgs();
    }
}