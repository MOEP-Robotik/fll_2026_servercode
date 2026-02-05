<?php
namespace Services;

use Core\CSV;
use Models\CSVData;
use Core\ImageList;
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

    private function getEmailContentConfirmation(string $vorname, string $nachname, string $coordinate, string $date, string $email, string $telephone, string $plz, string $timestamp): string {
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
        $base = str_replace('{{coordinate}}', htmlspecialchars($coordinate ?? ''), $base);
        $base = str_replace('{{date}}', htmlspecialchars($date ?? ''), $base);
        $base = str_replace('{{email}}', htmlspecialchars($email ?? ''), $base);
        $base = str_replace('{{telephone}}', htmlspecialchars($telephone ?? ''), $base);
        $base = str_replace('{{plz}}', htmlspecialchars($plz ?? ''), $base);
        $base = str_replace('{{timestamp}}', htmlspecialchars($timestamp ?? ''), $base);
        return $base;
    }

    private function buildAttachments(Submission $submission, Account $account): array {
        $imageList = new ImageList($submission->files);
        $returnArray = [];
        foreach ($imageList->get() as $image) {
            $returnArray[] = [
                'content' => base64_encode(file_get_contents($image->filepath)),
                'filename' => $image->UUID . "." . $image->extension,
            ];
        }
        $csvdata = new CSVData();
        $csvdata->material = $submission->material;
        $csvdata->coordinate = $submission->coordinate;
        $csvdata->email = $account->email;
        $csvdata->telephone = $account->telephone;
        $csvdata->date = $submission->date;

        $csv = new CSV($submission->user_id, "{$submission->id}.csv");
        $csv->open(true);
        $csv->writeOne($csvdata);
        $returnArray[] = [
            "content" => file_get_contents($csv->filepath),
            'filename' => $csv->filename
        ];
        $csv->close();
        return $returnArray;
    }

    public function sendConfirmation(Submission $submission, Account $account): void {
        $this->resend->emails->send([
            'from'    => $_ENV['EMAIL_SENDER'],
            'to'      => $account->email,
            'subject' => "Fundbeleg - {$submission->date} hinzugefügt",
            'html'    => $this->getEmailContentConfirmation(
                $account->vorname,
                $account->nachname,
                "Längengrad: " . $submission->coordinate->lon . " Breitengrad: " . $submission->coordinate->lat,
                $submission->date,
                $account->email,
                $account->telephone,
                $account->plz,
                \IntlDateFormatter::formatObject(new \DateTime(), "d. MMMM yyyy, HH:mm 'Uhr'", 'de_DE')
            ),
            'attachments' => $this->buildAttachments($submission, $account)
        ]);
    }

    public function getEmailContentLVR (string $vorname, string $nachname, string $coordinate, string $date, string $email, string $telephone, string $plz, string $timestamp): string { //TODO: make a better E-Mail
        $base = <<<'HTML'
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff">
                <tr>
                    <td style="color:#000000; font-size:14px; line-height:1.5;">
                        <p>Sehr geehrte Damen und Herren,</p>
                
                        <p>
                        Eine neue Fundmeldung ist soeben in unserem System eingegangen. Hier sind die Informationen. Im Anhang finden Sie die Daten in Form einer ".csv"-Datei und die Bilder, falls welche vorhanden sind, im ".tif"-Format.
                        </p>
                
                        <p>
                        <strong>Zusammenfassung des Fundes:</strong>
                        </p>
                
                        <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:13px;">
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
                            <td style="border:1px solid #cccccc;"><strong>Zeitpunkt der Einreichung</strong></td>
                            <td style="border:1px solid #cccccc;">{{timestamp}}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #cccccc;"><strong>Übermittelte Dateien</strong></td>
                            <td style="border:1px solid #cccccc;">siehe Anhang</td>
                        </tr>
                        </table>
                
                        <p>
                        Bitte sichern Sie alle Dateien. Sollten Sie die Daten verlieren, kontaktieren sie uns gerne. 
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
        $base = str_replace('{{coordinate}}', htmlspecialchars($coordinate ?? ''), $base);
        $base = str_replace('{{date}}', htmlspecialchars($date ?? ''), $base);
        $base = str_replace('{{email}}', htmlspecialchars($email ?? ''), $base);
        $base = str_replace('{{telephone}}', htmlspecialchars($telephone ?? ''), $base);
        $base = str_replace('{{plz}}', htmlspecialchars($plz ?? ''), $base);
        $base = str_replace('{{timestamp}}', htmlspecialchars($timestamp ?? ''), $base);
        return $base;
    }

    public function sendLVR(Submission $submission, Account $account): void{
        $localeService = new LocaleService();
        $timestamp = \IntlDateFormatter::formatObject(new \DateTime(), "d. MMMM yyyy, HH:mm 'Uhr'", 'de_DE');
        $this->resend->emails->send([
            'from'    => $_ENV['EMAIL_SENDER'],
            'to'      => $localeService->getnearestemail(),
            'subject' => "Neuer Fund am - {$timestamp} eingegangen",
            'html'    => $this->getEmailContentLVR(
                $account->vorname,
                $account->nachname,
                "Längengrad: " . $submission->coordinate->lon . " Breitengrad: " . $submission->coordinate->lat,
                $submission->date,
                $account->email,
                $account->telephone,
                $account->plz,
                $timestamp
            ),
            'attachments' => $this->buildAttachments($submission, $account)
        ]);
    }
}