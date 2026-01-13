<?php
namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Submission;
use Models\Coordinate;
use Database\SubmissionDatabase;
use Services\MailService;

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
        }

        if (empty($data['coordinate']) || empty($data['coordinate']['lon']) || empty($data['coordinate']['lat'])) {
            Response::json(['message' => 'Coordinate missing or invalid'], 400);
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
        $submiss->files = $data['files'] ?? null;
        $submiss->timestamp = $data['timestamp'] ?? null;

        $repo = new SubmissionDatabase();
        $id = $repo->create($submiss);

        // TODO: Enable in production
        // (new MailService())->sendConfirmation($data['email']);

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
}
