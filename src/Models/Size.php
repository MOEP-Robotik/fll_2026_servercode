<?php
namespace Models;

class Size {
    public int | null $length;
    public int | null $width;
    public int | null $height;
    public int | null $weight;
    public function toString()
    {
        return "". $this->length ."". $this->width . "". $this->height ."". $this->weight;
    }
}