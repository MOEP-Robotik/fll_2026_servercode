<?php
namespace Services;
use Models\Coordinate;

class GemeindeService {
    public function getGemeinde(Coordinate $coordinate) {
        $lat = $coordinate->lat; 
        $lon = $coordinate->lon;

        $url = "https://nominatim.openstreetmap.org/reverse?" . http_build_query([
            "lat" => $lat,
            "lon" => $lon,
            "format" => "json",
            "zoom" => 10,
            "addressdetails" => 1
        ]);

        $options = [
            "http" => [
                "header" => "User-Agent: Archive(NonPublic)/1.0 (fllforschung@fll.gandalf2532.dev)"
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        $adresse = $data["address"] ?? [];

        // Gemeinde ermitteln (Fallbacks!)
        $gemeinde =
            $adresse["city"]
            ?? $adresse["town"]
            ?? $adresse["village"]
            ?? $adresse["municipality"]
            ?? null;

        return $gemeinde;
        }
}