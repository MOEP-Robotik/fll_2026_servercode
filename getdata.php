<?php
opcache_reset();

$allowedOrigins = [
    'https://gandalf2532.dev',     // deine Webseite
    'http://localhost:3000',       // evtl. lokal zum Entwickeln
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *"); // Für Apps oder andere Clients
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// JSON-Antwort erzwingen
header('Content-Type: application/json');
// Keine Browser-Cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Fehler-Logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// DB-Verbindung
$host = $_ENV['dbhost'];
$dbname = $_ENV['dbname'];
$dbuser = $_ENV['dbuser'];
$dbpass = $_ENV['dbpass'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM funde");
    $stmt->execute();
    $funde = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "funde" => $funde
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "DB-Fehler: " . $e->getMessage()]);
}
