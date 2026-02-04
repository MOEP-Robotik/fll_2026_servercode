<?php
namespace Models;

/*
Submissions (DB):
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    location TEXT,
    files TEXT,
    date TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER,
    FOREIGN KEY (user_id) REFERENCES users(id)
*/
class Submission {
    public int | null $id = null;
    public Coordinate $coordinate;
    public string $date;
    public string | null  $files;
    public string | null $timestamp;
    public int $user_id;
    public string $material;
    public int $length;
    public int $width;
    public int $height;
    public int $weight;
}