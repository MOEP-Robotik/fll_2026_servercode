<?php
namespace Models;

/*
Submissions (DB):
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    location TEXT,
    date TEXT,
    length INTEGER,
    width INTEGER,
    height INTEGER,
    weight INTEGER,
    files TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER,
    FOREIGN KEY (user_id) REFERENCES users(id)
*/
class Submission {
    public int | null $id = null;
    public Coordinate $coordinate;
    public string | null $comment = null; //noch nicht im Frontend
    public string | null $datierung = null; //noch nicht im Frontend
    public string | null $date = null;
    public string | null  $files;
    public string $material;
    public Size $size;
    public string | null $timestamp;
    public int $user_id;
    //public string $gemeinde; //bisher ignoriert weil noch keine Ahnung...
}