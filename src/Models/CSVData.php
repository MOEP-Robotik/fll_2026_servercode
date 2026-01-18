<?php
namespace Models;

/*
Data Class for the CSV Data
*/

class CSVData {
    public string $title;
    public string $description;
    public Coordinate $coordinate;
    public string $email;
    public string $telephone;
    public string $date;

    public function toArray(): array {
        return [$this->title, $this->description, $this->coordinate, $this->email, $this->telephone, $this->date];
    }
}