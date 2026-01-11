<?php
require __DIR__ . '/../../vendor/autoload.php';

use Mailgun\Mailgun;
use Dotenv\Dotenv;

class MailService
{
    private Mailgun $mg;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->mg = Mailgun::create(
            $_ENV['MAILGUN_API_KEY'],
            'https://api.eu.mailgun.net'
        );
    }

    public function sendConfirmation(string $email): void
    {
        $this->mg->messages()->send('lumentae.dev', [
            'from'    => 'fllforschung@lumentae.dev',
            'to'      => $email,
            'subject' => 'The PHP SDK is awesome!',
            'text'    => 'It is so simple to send a message.'
        ]);
    }
}