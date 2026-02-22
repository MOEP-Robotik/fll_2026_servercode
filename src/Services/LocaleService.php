<?php
namespace Services;

require __DIR__ . '/../../vendor/autoload.php';

use Models\Coordinate;

class LocaleService {
    public function getNearestEmail(Coordinate $cords): string {
        $gemeinden = require __DIR__ ."/Gemeindeliste.php";
        $gemeindeserv = new GemeindeService();
        $gemeinde = $gemeindeserv->getGemeinde($cords);
        return $gemeinden[$gemeinde]['email'];
    }
}