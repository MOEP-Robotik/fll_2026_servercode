<?php
require_once __DIR__ . '/../Core/Database.php';

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

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM submissions");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array | false {
        $stmt = $this->db->prepare("SELECT * FROM submissions WHERE id = :id");
        $stmt->execute([
            ':id' => $id
        ]);
        return $stmt->fetch();
    }
}
