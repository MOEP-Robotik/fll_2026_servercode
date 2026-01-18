<?php
namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Submission;
use Models\Coordinate;
use Database\SubmissionDatabase;
use Services\MailService;
use Core\CSV;
use Models\CSVData;

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
        $submiss->email = $data['email'];
        $submiss->address = $data['address'];
        $submiss->telephone = $data['telephone'];
        $submiss->date = $data['date'];
        $submiss->files = $data['files'] ?? null;

        $repo = new SubmissionDatabase();
        $id = $repo->create($submiss);

        (new MailService())->sendConfirmation($data['email'], $data['title']);

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

    public function exportCSV($submission_id): bool{ //gibt success zurück; könnte maybe den Dateipfad zurückgeben
        $repo = new SubmissionDatabase();
        $row = $repo->getById($submission_id);
        if (!$row){
            return false;
        }
        $coordinate = new Coordinate();
        $coordinate->lon = (float)$row->coordinate->lon;
        $coordinate->lat = (float)$row->coordinate->lat;

        $data = new CSVData();
        $data->title = $row->title;
        $data->description = $row->description;
        $data->coordinate = $coordinate;
        $data->email = $row->email;
        $data->telephone = $row->telephone;

        $csv = new CSV();
        $filename = 'submission_' . $submission_id . '.csv'; //TODO: konkreten Dateipfad festlegen
        $csv->open($filename);
        $csv->writeOne($data);
        return true;
    }
}
