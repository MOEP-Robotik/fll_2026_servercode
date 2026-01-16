<?php
namespace Services;

use Dotenv\Dotenv;

class MailService {
    private \Resend\Client $resend;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->resend = \Resend::client($_ENV['RESEND_API_KEY']);
    }

    public function sendConfirmation(string $email, string $title): void
    {
        $this->resend->emails->send([
            'from'    => $_ENV['EMAIL_SENDER'],
            'to'      => $email,
            'subject' => 'FLL 2026 - ' . $title . ' hinzugefügt',
            'html'    => '<p>Dein Fund "' . $title . '" wurde erfolgreich hinzugefügt.</p>'
        ]);
    }
}