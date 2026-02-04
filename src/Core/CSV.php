<?php
namespace Core;

require __DIR__ . '/../../vendor/autoload.php';

use Models\CSVData;

class CSV {
    private $stream = null;
    public ?string $filename = null;

    private array $head = ["title", "description", "longitude", "latitude", "email", "telephone", "date"];

    public function open(bool $clearFile = false): void {
        if ($this->filename === null) {
            throw new \Exception("Filename muss gesetzt werden");
        }

        try {
            if ($clearFile) {
                $this->stream = fopen($this->filename, "w");
                if ($this->stream === false){
                    throw new \Exception("Problem beim Öffnen der Datei");
                }

                fputcsv($this->stream, $this->head);
                return;
            }

            if (file_exists($this->filename)) {
                $check = fopen($this->filename, "r");
                $headFromFile = fgetcsv($check);
                fclose($check);

                if ($headFromFile !== $this->head) {
                    throw new \Exception("Datei mit falschem Header gewählt");
                }

                $this->stream = fopen($this->filename, "a");

                if ($this->stream === false) {
                    throw new \Exception("Problem beim Öffnen der Datei");
                }

            } else {
                $this->stream = fopen($this->filename, "w");

                if ($this->stream === false) {
                    throw new \Exception("Problem beim Öffnen der Datei");
                }

                fputcsv($this->stream, $this->head);
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
    }

    public function writeArr(array $data): bool {
        if ($this->stream === null) {
            throw new \Exception("File muss vorher geöffnet werden");
        }

        foreach ($data as $row) {
            if (!($row instanceof CSVData)) {
                throw new \Exception("Array enthält ein nicht CSVData Objekt");
            }
            fputcsv($this->stream, $row->toArray());
        }

        return true;
    }

    public function writeOne(CSVData $data): bool {
        if ($this->stream === null) {
            throw new \Exception("File muss vorher geöffnet werden");
        }

        fputcsv($this->stream, $data->toArray());
        return true;
    }

    public function close(): void {
        if ($this->stream !== null) {
            fclose($this->stream);
            $this->stream = null;
        }
    }
}