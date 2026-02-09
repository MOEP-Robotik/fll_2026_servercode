<?php
namespace Services;

use Locale;
use Models\Coordinate;

class LocaleService {
    public function getNearestEmail(Coordinate $cords): string {
        $gemeinden = require __DIR__ ."/Gemeindeliste.php";
        $gemeindeserv = new GemeindeService();
        $gemeinde = $gemeindeserv->getGemeinde($cords);
        return $gemeinden[$gemeinde]['email'];
    }
    public function dev() {
        return "fgier2010@gmail.com";
    }
}