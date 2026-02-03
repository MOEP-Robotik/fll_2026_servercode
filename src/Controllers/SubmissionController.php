<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;
use Core\Request;
use Core\Response;
use Database\AccountDatabase;
use Models\Submission;
use Models\Coordinate;
use Database\SubmissionDatabase;
use Services\MailService;
use Core\CSV;
use Models\CSVData;
use Controllers\ImageController;

class SubmissionController {
    public function submit(Request $request): void {
        if ($request->post()) {
            $this->new($request);
        } elseif ($request->get()) {
            $this->get($request);
        }
    }

    private function new(Request $request): void {
        // Unterstütze sowohl JSON als auch FormData
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $data = $request->json();
        } else {
            // FormData verarbeiten
            $postData = $request->postData();
            
            // Koordinaten aus verschiedenen Formaten extrahieren
            $lon = null;
            $lat = null;
            
            // Versuche verschachteltes Array (wenn PHP es automatisch geparst hat)
            if (isset($postData['coordinate']) && is_array($postData['coordinate'])) {
                $lon = $postData['coordinate']['lon'] ?? null;
                $lat = $postData['coordinate']['lat'] ?? null;
            }
            
            // Falls nicht, versuche bracket-Notation (coordinate[lon])
            if ($lon === null && isset($postData['coordinate[lon]'])) {
                $lon = $postData['coordinate[lon]'];
            }
            if ($lat === null && isset($postData['coordinate[lat]'])) {
                $lat = $postData['coordinate[lat]'];
            }
            
            $data = [
                'title' => $postData['title'] ?? '',
                'description' => $postData['description'] ?? '',
                'coordinate' => [
                    'lon' => $lon,
                    'lat' => $lat,
                ],
                'date' => $postData['date'] ?? null,
            ];
            $headers = $request->header();
        }

        // Prüfe, ob jwt_token vorhanden ist
        if (empty($headers['Authorization'])) {
            Response::json(["message" => "Authorization required: JWT token missing"], 401);
            return;
        }

        $auth = new Auth();
        $valid = $auth->validate_JWT($headers['Authorization']);
        if (!$valid) {
            Response::json(["message" => "Authorization required: Invalid JWT"], 401);
            return;
        }

        $user_id = $auth->getUserIdFromJWT($headers['Authorization']);
        if (!$user_id) {
            Response::json(["message" => "Invalid user id"], 400);
            return;
        }

        $accountdb = new AccountDatabase();
        $user = $accountdb->getById($user_id);
        if (!$user) {
            Response::json(['message' => 'User not found'], 404);
            return;
        }

        if (empty($data['title'])) {
            Response::json(['message' => 'Title missing'], 400);
            return;
        }

        // Prüfe Koordinaten: empty(0) würde true zurückgeben, daher explizit auf null prüfen
        if (!isset($data['coordinate']) || !is_array($data['coordinate'])) {
            Response::json(['message' => 'Coordinate missing or invalid'], 400);
            return;
        }

        if (!isset($data['coordinate']['lon']) || !isset($data['coordinate']['lat'])) {
            Response::json(['message' => 'Coordinate missing or invalid'], 400);
            return;
        }

        // Prüfe, ob Koordinaten numerisch sind (0 ist ein gültiger Wert)
        if (!is_numeric($data['coordinate']['lon']) || !is_numeric($data['coordinate']['lat'])) {
            Response::json(['message' => 'Coordinate missing or invalid'], 400);
            return;
        }

        $coordinate = new Coordinate();
        $coordinate->lon = (float)$data['coordinate']['lon'];
        $coordinate->lat = (float)$data['coordinate']['lat'];

        $submiss = new Submission();
        $submiss->id = $data['id'] ?? null;
        $submiss->title = $data['title'];
        $submiss->description = $data['description'] ?? '';
        $submiss->coordinate = $coordinate;
        $submiss->date = $data['date'];

        // Bilder verarbeiten, falls vorhanden
        $files = $request->files();
        if (!empty($files)) {
            try {
                $imgs = new ImageController($user_id);# lieber mit der submission_id irgendwie machen
                $imgs->uploadImgs($files);
                $submiss->files = $imgs->images->toJSON();
            } catch (\Exception $e) {
                Response::json(['message' => 'Error uploading images: ' . $e->getMessage()], 400);
                return;
            }
        }

        $repo = new SubmissionDatabase();
        $id = $repo->create($submiss);
        $user->funde[] = $id;
        $accountdb->updateFunde($user);

        new MailService()->sendConfirmation($submiss, $user);

        Response::json(['id' => $id]);
    }

    private function get(Request $request): void {
        $repo = new SubmissionDatabase();

        $parts = explode('/', $request->path());
        if (\count($parts) > 3) {
            $id = \intval($parts[3]);
            $submission = $repo->getById($id);
            if (!$submission) {
                Response::json([
                    'message' => 'Submission not found'
                ], 404);
                return;
            }
            Response::json($submission);
            return;
        } else {
            $headers = $request->header();
            $auth = new AuthController();
            $userId = $auth->getUserId($headers['Authorization'] ?? '');
            $submissions = $repo->getAll($userId);
            if (!$submissions) {
                Response::json([
                    'message' => 'No submissions found'
                ], 404);
                return;
            }
            Response::json($submissions);
            return;
        }
    }

    public function exportCSV(int $submission_id): bool { //gibt success zurück; könnte maybe den Dateipfad zurückgeben
        $repo = new SubmissionDatabase();
        $row = $repo->getById($submission_id);
        if (!$row) {
            return false;
        }
        $coordinate = new Coordinate();
        $coordinate->lon = (float)$row->coordinate->lon;
        $coordinate->lat = (float)$row->coordinate->lat;

        $data = new CSVData();
        $data->title = $row->title;
        $data->description = $row->description;
        $data->coordinate = $coordinate;
        /*$data->email = $row->email;
        $data->telephone = $row->telephone;*/

        $csv = new CSV();
        $filename = "submission_$submission_id.csv"; //TODO: konkreten Dateipfad festlegen

        try {
            $csv->open($filename);
            $csv->writeOne($data);
            $csv->close();
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }
}
