<?php
namespace Core;

use Models\CSVData;

//Kann nur zum Schreiben verwendet werden; auch nur für diesen Anwendungsweck, der geplant ist
class CSV {
    private $stream;
    private $head = ["name", "ort"]; //TODO: entscheiden, was da rein muss

    public function open(string $filename, bool $clearFile = false): void {
        try{
            if ($clearFile){
                $this->stream = fopen($filename, "w");
                fputcsv($this->stream, $this->head);
            } else{
                if (file_exists($filename)){
                    $this->stream = fopen($filename, "r");
                    $headFromFile = fgets($this->stream);
                    fclose($this->stream);
                    if ($headFromFile == $this->head) {
                        $this->stream = fopen($filename, "a");
                    } else {
                        throw new \Exception("Datei mit falschem head gewählt");
                    }
                } else {
                    fopen($filename,"w");
                }
            }
        } catch (\Throwable $e){
            error_log($e);
        }
    }

    public function writeArr(array $data): bool { //gibt "success" zurück
        try{
            if ($data && is_array($data)){
                foreach($data as $row){
                    fputcsv($this->stream, $row);
                }
            }
            return true;
        } catch (\Throwable $e){
            error_log($e);
            return false;
        }
    }

    public function writeOne(CSVData $data): bool { //gibt "success" zurück; Kein Plan ob das funktioniert
        try {
            if ($data) {
                fputcsv($this->stream, (array)$data);
            }
            return true;
        } catch (\Throwable $e) {
            error_log($e);
            return false;
        }
    }
}