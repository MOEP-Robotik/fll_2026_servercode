<?php
namespace Models;

/*
Account (DB):
    id: int (von DB generiert)
    vorname: text
    nachname: text
    passhash: text
    plz: text
    email: text
    telefonnummer: text
    funde: array<submission_id> (-> array<id>)
*/
class Account {
    public int | null $id;
    public string $vorname;
    public string $nachname;
    public string $passhash;
    public int $plz;
    public string $email;
    public string $telephone;
    public array $funde;
}