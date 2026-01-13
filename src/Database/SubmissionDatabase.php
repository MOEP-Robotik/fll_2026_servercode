<?php
namespace Database;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Database;
use Models\Coordinate;
use Models\Submission;

class SubmissionDatabase {
    private $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function create(Submission $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO submissions (title, description, location, email) VALUES (:t, :d, :l, :e)"
        );
        $location = json_encode([
            'lon' => $data['lon'],
            'lat' => $data['lat']
        ]);
        //TODO: irgendwo file-UUIDs generieren und in array speichern und dann einfügen
        $stmt->execute([
            ':t' => $data['title'],
            ':d' => $data['description'] ?? '',
            ':l' => $location,
            ':e' => $data['email']
        ]);

        return (int)$this->db->lastInsertId();
    }

    //returns Array with Submissions
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM submissions");
        $rows = $stmt->fetchAll();
        $submissions = [];
        foreach ($rows as $row) {
            $coordData = json_decode($row['location'], true);
            $location = new Coordinate($coordData['lon'], $coordData['lat']);

            $submissions[] = new Submission(
                (int)$row['id'],
                (string)$row['title'],
                (string)$row['description'],
                $location,
                (string)$row['email'],
                (array)$row['files'],
                (string)$row['timestamp']
            );
        }
        return $submissions;
    }

    public function getById(int $id): Submission | false {
        $stmt = $this->db->prepare("SELECT * FROM submissions WHERE id = :id");
        $stmt->execute([
            ':id' => $id
        ]);
        $row = $stmt->fetch();
        $coordData = json_decode($row['location'], true);
        $location = new Coordinate($coordData['lon'], $coordData['lat']);
        return new Submission(
            (int)$row['id'],
            (string)$row['title'],
            (string)$row['description'],
            $location,
            (string)$row['email'],
            (string)$row['filepath'],
            (string)$row['timestamp']
        );
    }
}
