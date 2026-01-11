<?php
require_once __DIR__  . './Coordinate.php';
/*
Submissions (DB):
    id: int (von DB generiert)
    title: text
    description: text
    location: coordinate
    email: text
    filepath: text
    created_at: timestamp (von DB generiert)
*/
class Submission {
    public int | null $id = null;
    public string $title;
    public string $description;
    public Coordinate $coordinate;
    public string $email;
    public string | null $filepath;
    public string | null $timestamp;
}