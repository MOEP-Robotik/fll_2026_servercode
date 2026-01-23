<?php
namespace Controllers;

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
        $data = $request->json();

        $auth = new Auth();
        $valid = $auth->validate_JWT($data["jwt_token"]);
        if (!$valid) {
            Response::json(["message" => "Authorization required: Invalid JWT"], 401);
            return;
        }

        $user_id = $auth->getUserIdFromJWT($data["jwt_token"]);
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

        if (empty($data['coordinate']) || empty($data['coordinate']['lon']) || empty($data['coordinate']['lat'])) {
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

        $user_id = $auth->getUserIdFromJWT($data["jwt_token"]);
        $imgs = new ImageController($user_id);
        $imgs->uploadImgs($request->files(), $user_id);

        $submiss->files = $imgs->images ?? null;

        $repo = new SubmissionDatabase();
        $id = $repo->create($submiss);

        new MailService()->sendConfirmation($submiss, $user);

        Response::json(['id' => $id]);
    }

    private function get(Request $request): void {
        $repo = new SubmissionDatabase();

        $parts = explode('/', $request->path());
        if (count($parts) > 3) {
            $id = intval($parts[3]);
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
            $submissions = $repo->getAll();
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
        $filename = 'submission_' . $submission_id . '.csv'; //TODO: konkreten Dateipfad festlegen

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
