<?php
namespace Services;

use Dotenv\Dotenv;
use Models\Account;
use Models\Submission;

class MailService {
    private \Resend\Client $resend;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->resend = \Resend::client($_ENV['RESEND_API_KEY']);
    }

    public function getEmailContent(string $vorname, string $nachname, string $title, string $description, string $coordinate, string $date, string $email, string $telephone, string $plz, string $timestamp): string {
        $base = <<<'HTML'
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff">
                <tr>
                    <td style="color:#000000; font-size:14px; line-height:1.5;">
                        <p>Sehr geehrte/r {{vorname}} {{nachname}},</p>
                
                        <p>
                        Vielen Dank für die Übermittlung Ihrer Fundmeldung über unsere App.
                        Hiermit bestätigen wir den erfolgreichen Eingang Ihrer Angaben.
                        </p>
                
                        <p>
                        <strong>Zusammenfassung Ihrer Einreichung:</strong>
                        </p>
                
                        <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:13px;">
                        <tr>
                            <td width="35%" style="border:1px solid #cccccc;"><strong>Titel des Fundes</strong></td>
                            <td style="border:1px solid #cccccc;">{{title}}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>Beschreibung</strong></td>
                            <td style="border:1px solid #cccccc;">{{description}}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>Fundkoordinaten</strong></td>
                            <td style="border:1px solid #cccccc;">{{coordinate}}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>Funddatum</strong></td>
                            <td style="border:1px solid #cccccc;">{{date}}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>E-Mail-Adresse</strong></td>
                            <td style="border:1px solid #cccccc;">{{email}}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>Telefonnummer</strong></td>
                            <td style="border:1px solid #cccccc;">{{telephone}}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>Postleitzahl</strong></td>
                            <td style="border:1px solid #cccccc;">{{plz}}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>Übermittelte Dateien</strong></td>
                            <td style="border:1px solid #cccccc;">siehe Anhang</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>Zeitpunkt der Einreichung</strong></td>
                            <td style="border:1px solid #cccccc;">{{timestamp}}</td>
                        </tr>
                        </table>
                
                        <p>
                        Bitte bewahren Sie diese E-Mail als Beleg Ihrer Einreichung auf.
                        Sollten Rückfragen bestehen, werden wir Sie über die angegebenen Kontaktdaten kontaktieren.
                        </p>
                
                        <p style="font-size:12px; color:#555555;">
                        Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese Nachricht.
                        </p>
                
                        <p>Mit freundlichen Grüßen,<br>
                        MÖP Robotik
                        </p>
                    </td>
                </tr>
            </table>
        HTML;
        $base = str_replace('{{vorname}}', htmlspecialchars($vorname ?? ''), $base);
        $base = str_replace('{{nachname}}', htmlspecialchars($nachname ?? ''), $base);
        $base = str_replace('{{title}}', htmlspecialchars($title ?? ''), $base);
        $base = str_replace('{{description}}', nl2br(htmlspecialchars($description ?? '')), $base);
        $base = str_replace('{{coordinate}}', htmlspecialchars($coordinate ?? ''), $base);
        $base = str_replace('{{date}}', htmlspecialchars($date ?? ''), $base);
        $base = str_replace('{{email}}', htmlspecialchars($email ?? ''), $base);
        $base = str_replace('{{telephone}}', htmlspecialchars($telephone ?? ''), $base);
        $base = str_replace('{{plz}}', htmlspecialchars($plz ?? ''), $base);
        $base = str_replace('{{timestamp}}', htmlspecialchars($timestamp ?? ''), $base);
        return $base;
    }

    public function sendConfirmation(Submission $submission, Account $account): void {
        $this->resend->emails->send([
            'from'    => $_ENV['EMAIL_SENDER'],
            'to'      => $account->email,
            'subject' => "Fundbeleg - {$submission->title} hinzugefügt",
            'html'    => $this->getEmailContent(
                $account->vorname,
                $account->nachname,
                $submission->title,
                $submission->description,
                "Längengrad: " . $submission->coordinate->lat . " Breitengrad: " . $submission->coordinate->lon,
                $submission->date,
                $account->email,
                $account->telefonnummer,
                $account->plz,
                date("F j, Y, g:i a")
            ),
        ]);
    }
}