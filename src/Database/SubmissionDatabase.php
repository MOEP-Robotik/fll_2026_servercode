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
}
