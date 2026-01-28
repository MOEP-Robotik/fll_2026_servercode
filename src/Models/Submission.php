<?php
namespace Models;

use Core\Imagelist;

/*
Submissions (DB):
    id: int (von DB generiert)
    title: text
    description: text
    location: coordinate
    date: text
    files: JSON-Objekt mit Array
    created_at: timestamp (von DB generiert)
*/
class Submission {
    public int | null $id = null;
    public string $title;
    public string $description;
    public Coordinate $coordinate;
    public string $date;
    public string | null  $files;
    public string | null $timestamp;

}