<?php
namespace Models;

/*
Data Class for the CSV Data
*/

class CSVData {
    public Coordinate $coordinate;
    public string $email;
    public string $telephone;
    public string $date;
    public int $user_id;
    public string $material;

    public function toArray(): array {
        return [
            $this->material, 
            $this->coordinate->lon, 
            $this->coordinate->lat, 
            $this->email, 
            $this->telephone, 
            $this->date, 
            $this->user_id
            ];
    }
}