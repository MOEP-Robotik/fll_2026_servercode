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
use Models\Size;
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
        $contentType = $_SERVER['CONTENT_TYPE'] ?? ''; //JSON
        if (strpos($contentType, 'application/json') !== false) {
            $data = $request->json();
        } else {
            // FormData verarbeiten
            $postData = $request->postData();

            $lon = null;
            $lat = null;

            if (isset($postData['coordinate']) && is_array($postData['coordinate'])) {
                $lon = $postData['coordinate']['lon'] ?? null;
                $lat = $postData['coordinate']['lat'] ?? null;
            }
            
            if ($lon === null && isset($postData['coordinate[lon]'])) {
                $lon = $postData['coordinate[lon]'];
            }
            if ($lat === null && isset($postData['coordinate[lat]'])) {
                $lat = $postData['coordinate[lat]'];
            }
            
            // Guest-Daten extrahieren
            $guest = null;
            if (isset($postData['guest']) && is_array($postData['guest'])) {
                $guest = $postData['guest'];
            } else if (isset($postData['guest[vorname]'])) {
                $guest = [
                    'vorname' => $postData['guest[vorname]'] ?? '',
                    'nachname' => $postData['guest[nachname]'] ?? '',
                    'email' => $postData['guest[email]'] ?? '',
                    'plz' => $postData['guest[plz]'] ?? '',
                    'telefonnummer' => $postData['guest[telefonnummer]'] ?? '',
                ];
            }

            $data = [
                'title' => $postData['title'] ?? '',
                'description' => $postData['description'] ?? '',
                'coordinate' => [
                    'lon' => $lon,
                    'lat' => $lat,
                ],
                'date' => $postData['date'] ?? null,
                'guest' => $guest,
            ];
            $headers = $request->header();

            // Prüfe, ob jwt_token vorhanden ist
            if (empty($headers['Authorization'])) {
                Response::json(["message" => "Authorization required: JWT token missing"], 401);
                return;
            }
            // prüft, ob der JWT gültig ist
            $auth = new Auth();
            $valid = $auth->validate_JWT($headers['Authorization']);
            if (!$valid) {
                Response::json(["message" => "Authorization required: Invalid JWT"], 401);
                return;
            }
            // Extrahiere user_id aus dem JWT
            $user_id = $auth->getUserIdFromJWT($headers['Authorization']);
            if (!$user_id) {
                Response::json(["message" => "Invalid user id"], 400);
                return;
            }

            $sizedata = json_decode($postData['size'], true);
            $size = new Size();
            $size->length = $sizedata['length'];
            $size->width = $sizedata['width'];
            $size->height = $sizedata['height'];
            $size->weight = $sizedata['weight'];
            
            $data = [
                'coordinate' => [
                    'lon' => $lon,
                    'lat' => $lat,
                ],
                'date' => $postData['date'] ?? null,
                'files' => $postData['files'] ?? null,
                'material' => $postData['material'],
                'size' => $size,
                'user_id' => $user_id,
            ];
        }

        $accountdb = new AccountDatabase();
        $user = $accountdb->getById($user_id);
        if (!$user) {
            Response::json(['message' => 'User not found'], 404);
            return;
        }

        // Guest-Daten verarbeiten
        $isGuest = !empty($data['guest']);
        if ($isGuest) {
            $guest = $data['guest'];
            if (empty($guest['vorname']) || empty($guest['nachname']) || empty($guest['email']) || empty($guest['plz']) || empty($guest['telefonnummer'])) {
                Response::json(["message" => "Alle Gastfelder sind erforderlich (Vorname, Nachname, E-Mail, PLZ, Telefonnummer)"], 400);
                return;
            }

            // Account-Daten mit Guest-Daten aktualisieren
            $user->vorname = $guest['vorname'];
            $user->nachname = $guest['nachname'];
            $user->email = $guest['email'];
            $user->plz = (int)$guest['plz'];
            $user->telefonnummer = $guest['telefonnummer'];
        }

        if (empty($data['title'])) {
            Response::json(['message' => 'Title missing'], 400);
            return;
        }

        // Prüfe Koordinaten: empty würde true zurückgeben, daher explizit auf null prüfen
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
        $submiss->coordinate = $coordinate;
        $submiss->date = $data['date'];
        $submiss->files = $data['files'] ?? null;
        $submiss->material = $data['material']; 
        $submiss->user_id = $user_id;
        $submiss->size = $size;

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
        if ($isGuest) {
            $accountdb->update($user);
        } else {
            $accountdb->updateFunde($user);
        }

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
        $data->coordinate = $coordinate;
        $data->date = $row->date;
        $data->user_id = $row->user_id;
        $data->material = $row->material;

        $csv = new CSV();
        $filename = "submission_" . (string)$submission_id . ".csv"; //TODO: konkreten Dateipfad festlegen

        try {
            $csv->filename = $filename;
            $csv->open(false);
            $csv->writeOne($data);
            $csv->close();
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }
}
