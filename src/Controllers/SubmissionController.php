<?php
use Core\Request;
use Core\Response;

require_once __DIR__ . '/../Database/SubmissionDatabase.php';
require_once __DIR__ . '/../Services/MailService.php';

class SubmissionController {
    public function submit(Request $request): void {
        $data = $request->json();

        if (empty($data['title'])) {
            Response::json(['error' => 'Title missing'], 400);
        }

        $repo = new SubmissionDatabase();
        $id = $repo->create($data);

        // TODO: Enable in production
        // (new MailService())->sendConfirmation($data['email']);

        Response::json(['id' => $id]);
    }
}
