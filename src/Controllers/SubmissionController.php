<?php
namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Submission;
use Database\SubmissionDatabase;
use Servie\MailService;

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

        $repo = new SubmissionDatabase();
        $submiss = new Submission();
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
