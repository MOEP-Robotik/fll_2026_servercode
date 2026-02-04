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
            "INSERT INTO submissions (location, date, files, user_id) VALUES (:l, :d, :f, :u)"
        );
        $location = json_encode([
            'lon' => $data->coordinate->lon,
            'lat' => $data->coordinate->lat
        ]);
        $files = json_encode($data->files);
        $stmt->execute([
            ':l' => $location,
            ':d' => $data->date,
            ':f' => $files,
            ':u' => $data->user_id
        ]);

        return $this->db->lastInsertId();
    }

    //returns Array with Submissions
    public function getAll(int $userId): array {
        $userquery = $this->db->query("SELECT funde FROM users WHERE id = :id LIMIT 1");
        $userquery->execute([
            ':id' => $userId
        ]);
        $row = $userquery->fetch();
        if (!$row) {
            error_log("WTF (siehe SubmissionController9");
            return [];
        }
        $rows = [];
        $funde = json_decode($row['funde'], true);
        
        $stmt = $this->db->query("SELECT * FROM submissions WHERE id = :id LIMIT 1");
        foreach ($funde as $fund) {
            $stmt->execute([
                ":id" => $fund
            ]);
            $rows[] = $stmt->fetch();
        }
        $submissions = [];
        foreach ($rows as $row) {
            $coordData = json_decode($row['location'], true);
            $location = new Coordinate();
            $location->lon = (float)$coordData['lon'];
            $location->lat = (float)$coordData['lat'];

            $submission = new Submission();
            $submission->id = (int)$row['id'];
            $submission->coordinate = $location;
            $submission->files = $row['files'] ?? null;
            $submission->timestamp = (string)$row['created_at'];
            $submission->date = (string)$row['date'];
            $submission->user_id = (int)$row['user_id'];

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
        $submission->coordinate = $location;
        $submission->files = $row['files'] ?? null;
        $submission->timestamp = (string)$row['created_at'];
        $submission->date = (string)$row['date'];
        $submission->user_id = (int)$row['user_id'];

        return $submission;
    }
}
