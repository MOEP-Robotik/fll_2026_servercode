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
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
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

        if (strpos($contentType, 'application/json') !== false) {
            $data = $request->json();
        } else {
            // FormData verarbeiten - automatisch nested arrays parsen
            $data = $request->formData();
            
            // Size aus JSON string parsen falls vorhanden
            if (isset($data['size']) && is_string($data['size'])) {
                $sizedata = json_decode($data['size'], true);

                // Validierung: Nur bei erfolgreichem JSON-Decode und Array-Struktur ein Size-Objekt erstellen
                if (json_last_error() === JSON_ERROR_NONE && is_array($sizedata)) {
                    $size = new Size();
                    $size->length = $sizedata['length'] ?? null;
                    $size->width = $sizedata['width'] ?? null;
                    $size->height = $sizedata['height'] ?? null;
                    $size->weight = $sizedata['weight'] ?? null;
                    $data['size'] = $size;
                }
            }
        }
        
        $data['user_id'] = $user_id;

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

            if (!filter_var($guest['email'], FILTER_VALIDATE_EMAIL)) {
                Response::json(["message" => "Ungültige E-Mail-Adresse für Gast angegeben"], 400);
                return;
            }
            // Account-Daten mit Guest-Daten aktualisieren
            $user->vorname = $guest['vorname'];
            $user->nachname = $guest['nachname'];
            $user->email = $guest['email'];
            $user->plz = (int)$guest['plz'];
            $user->telephone = $guest['telefonnummer'];
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
        $submiss->date = $data['date'] ?? null;
        $submiss->files = $data['files'] ?? null;
        $submiss->material = $data['material'] ?? ""; 
        $submiss->user_id = $user_id;
        $submiss->size = $data['size'] ?? new Size();
        //$submiss->comment = $data['comment'] ?? null;
        //$submiss->datierung = $data['datierung'];
        $submiss->user_id = $user_id;

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

        $submiss->id = $id;
        $mailS = new MailService();
        $mailS->sendConfirmation($submiss, $user);
        $mailS->sendLVR($submiss, $user);

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
}
