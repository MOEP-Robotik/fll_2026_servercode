<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// DB-Verbindung
$host = $_ENV['dbhost'];
$dbname = $_ENV['dbname'];
$dbuser = $_ENV['dbuser'];
$dbpass = $_ENV['dbpass'];

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Prüfen, ob Bilder hochgeladen wurden
if (isset($_FILES['image'])) {
    $totalFiles = count($_FILES['image']['name']);
    for ($i = 0; $i < $totalFiles; $i++) {
        if ($_FILES['image']['error'][$i] === 0) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $fileName = time() . "_" . basename($_FILES['image']['name'][$i]);
            $targetFilePath = $targetDir . $fileName;

            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $targetFilePath)) {
                    $stmt = $conn->prepare("INSERT INTO bilder (pfad) VALUES (?)");
                    $stmt->bind_param("s", $targetFilePath);
                    $stmt->execute();
                    $stmt->close();

                    echo "Bild $fileName erfolgreich hochgeladen!<br>";
                } else {
                    echo "Fehler beim Hochladen von " . $_FILES['image']['name'][$i] . "<br>";
                }
            } else {
                echo "Dateityp nicht erlaubt: " . $_FILES['image']['name'][$i] . "<br>";
            }
        } else {
            echo "Fehler bei Datei: " . $_FILES['image']['name'][$i] . "<br>";
        }
    }
}

$conn->close();
