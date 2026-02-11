<?php
namespace Models;
//Mit Dr. Claßen absprechen
/*
Data Class for the CSV Data
*/

class CSVData {
    public string $material;
    public Coordinate $coordinate;
    public string $date;
    public string | null $comment;
    public Size $size;
    public string | null $datierung;
    public string $vorname;
    public string $nachname;
    public int $user_id; //Für unsere Entwicklung ????
    public function toArray(): array {
        $comment = "{$this->comment} Der Gegenstand ist {$this->size->length} cm lang, {$this->size->width} cm breit und {$this->size->height} cm hoch.";
        $ansprache = "{$this->nachname},{$this->vorname}";
        return [
            'Rechts' => $this->coordinate->lon, 
            'Hoch' => $this->coordinate->lat, 
            'Hoehe' => "",
            'Matkuerzel' => "",
            'Gewicht' => $this->size->weight,
            'Material' => $this->material, 
            'Kommentar' => $comment,
            'Datkode' => "",
            'Datierung' => $this->datierung,
            'Datum' => $this->date,
            'AnspracheVon' => $ansprache,
        ];
    }
}