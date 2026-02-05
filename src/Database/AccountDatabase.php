<?php
namespace Database;

require __DIR__ . '/../../vendor/autoload.php'; //Muss das wirklich so überall rein?

use Core\Database;
use Models\Account;
use Models\PartialAccount;

class AccountDatabase{
    private $db;

    public function __construct()
    {
        $this->db = Database::get();
    }
    public function getByEmail(string $email): PartialAccount | false {
        $stmt = $this->db->prepare("SELECT id, passhash FROM users WHERE email = :email LIMIT 1;");
        $stmt->execute([
            ":email" => $email
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        $account = new PartialAccount();
        $account->id = $row['id'];
        $account->passhash = $row['passhash'];

        return $account;
    }

    public function getById(int $id): Account | false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1;");
        $stmt->execute([
            ':id' => $id
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }

        $account = new Account();
        $account->id = $row['id'];
        $account->vorname = $row['vorname'];
        $account->nachname = $row['nachname'];
        $account->passhash = $row['passhash'];
        $account->plz = $row['plz'];
        $account->email = $row['email'];
        $account->telephone = $row['telefonnummer'];
        $account->funde = json_decode($row['funde']);  //TODO: wird vllt. noch nicht richtig geparsed 

        return $account;
    }

    public function create(Account $account): int {
        $stmt = $this->db->prepare("INSERT INTO users (vorname, nachname, passhash, plz, email, telefonnummer, funde) VALUES (:vorname, :nachname, :passhash, :plz, :email, :telefonnummer, :funde)");
        $result = $stmt->execute([
            ':vorname' => $account->vorname,
            ':nachname' => $account->nachname,
            ':passhash' => $account->passhash,
            ':plz' => $account->plz,
            ':email' => $account->email,
            ':telefonnummer' => $account->telephone,
            ':funde' => json_encode($account->funde)
        ]);
        if (!$result) {
            error_log("Failed to create account for " . $account->email);
            return false;
        }
        return $this->db->lastInsertId();
    }

    public function updateFunde(Account $account): bool {
        $stmt = $this->db->prepare("UPDATE users SET funde = :funde WHERE id = :id");
        $result = $stmt->execute([
            ':funde' => json_encode($account->funde),
            ':id' => $account->id
        ]);
        return $result;
    }
}