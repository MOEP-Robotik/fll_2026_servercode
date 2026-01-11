<?php
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/Submission.php';
require_once __DIR__ . '/../Models/Coordinate.php';

class SubmissionDatabase {
    private $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO submissions (title, description) VALUES (:t, :d)"
        );
        $stmt->execute([
            ':t' => $data['title'],
            ':d' => $data['description'] ?? ''
        ]);

        return (int)$this->db->lastInsertId();
    }

    #returns Array with Submissions
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM submissions");
        $rows = $stmt->fetchAll();
        $submissions = [];
        foreach ($rows as $row) {
            $coordData = json_decode($row['location'], true);
            $location = new Coordinate($coordData['lon'], $coordData['´lon']);

            $submissions[] = new Submission(
                (int)$row['id'],
                (string)$row['title'],
                (string)$row['description'],
                $location,
                (string)$row['email'],
                (string)$row['filepath'],
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
        return $stmt->fetch();
    }
}
