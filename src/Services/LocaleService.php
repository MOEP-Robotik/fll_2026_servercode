<?php
namespace Services;

require __DIR__ . '/../../vendor/autoload.php';

use Models\Coordinate;
use Dotenv\Dotenv;

class LocaleService {
    public function getNearestEmail(Coordinate $cords): string {
        $gemeinden = require __DIR__ ."/Gemeindeliste.php";
        $gemeindeserv = new GemeindeService();
        $gemeinde = $gemeindeserv->getGemeinde($cords);
        return $gemeinden[$gemeinde]['email'];
    }
    public function dev(): string {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $email = $_ENV['devmail'];
        return $email;
    }
}