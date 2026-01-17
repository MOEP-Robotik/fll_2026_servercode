<?php
namespace Models;

/*
Inforamtion, which is given to use for the Account screen and maybe later for creation of the .csv/.<other type of file, the LVR would take> file
Literally just an Account without 
    $id 
and 
    $passhash
*/

class UserInfo{
    public string $vorname;
    public string $nachname;
    public int $plz;
    public string $email;
    public string $telefonnummer;
    public array $funde;
}