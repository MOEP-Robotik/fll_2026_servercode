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
            "INSERT INTO submissions (title, description, location, date, files) VALUES (:t, :d, :l, :z, :f)"
        );
        $location = json_encode([
            'lon' => $data->coordinate->lon,
            'lat' => $data->coordinate->lat
        ]);
        $files = json_encode($data->files);
        $stmt->execute([
            ':t' => $data->title,
            ':d' => $data->description ?? '',
            ':l' => $location,
            ':z' => $data->date,
            ':f' => $files
        ]);

        return $this->db->lastInsertId();
    }

    //returns Array with Submissions
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM submissions");
        $rows = $stmt->fetchAll();
        $submissions = [];
        foreach ($rows as $row) {
            $coordData = json_decode($row['location'], true);
            $location = new Coordinate();
            $location->lon = (float)$coordData['lon'];
            $location->lat = (float)$coordData['lat'];

            $submission = new Submission();
            $submission->id = (int)$row['id'];
            $submission->title = (string)$row['title'];
            $submission->description = (string)$row['description'];
            $submission->coordinate = $location;
            $submission->files = $row['files'] ?? null;
            $submission->timestamp = (string)$row['timestamp'];

            $submissions[] = $submission;
        }
        return $submissions;
    }

    public function getById(int $id): Submission | false {
        $stmt = $this->db->prepare("SELECT * FROM submissions WHERE id = :id LIMIT 1");
        $stmt->execute([
            ':id' => $id
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        $coordData = json_decode($row['location'], true);
        $location = new Coordinate();
        $location->lon = (float)$coordData['lon'];
        $location->lat = (float)$coordData['lat'];

        $submission = new Submission();
        $submission->id = (int)$row['id'];
        $submission->title = (string)$row['title'];
        $submission->description = (string)$row['description'];
        $submission->coordinate = $location;
        $submission->files = $row['files'] ?? null;
        $submission->timestamp = (string)$row['created_at'];

        return $submission;
    }
}
