<?php
namespace Models;
//Mit Dr. Claßen absprechen
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
    //public string $name; //Wirklich???

    public function toArray(): array {
        return [
            'material' => $this->material, 
            'lon' => $this->coordinate->lon, 
            'lat' => $this->coordinate->lat, 
            'email' => $this->email, 
            'telephone' => $this->telephone, 
            'date' => $this->date, 
            'user_id' => $this->user_id
            ];
    }
}