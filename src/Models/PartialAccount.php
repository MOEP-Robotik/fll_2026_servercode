<?php
namespace Models;
//Das ist (noch) nur für die Authentification !!!
/*
Account (DB):
    id: int (von DB generiert)
    vorname: text
    nachname: text
    passhash: text
    email: text
    plz: text
    telefonnummer: text
    funde: array<submission_id> (-> array<id>)
*/

class PartialAccount{
    public int $id; //muss nicht null sein können, weil der Account definitiv schon existieren muss
    public string $passhash;
    public string $email;
}
