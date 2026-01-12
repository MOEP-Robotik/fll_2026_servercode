<?php
namespace Models;

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
    public array | null $files;
    public string | null $timestamp;
}