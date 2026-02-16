<?php
namespace Core;

require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private $jwtkey;
    function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->jwtkey = $_ENV['jwtsecret'];
    }

    public function generateJWT(int $user_id): string {
        $issuedAt = time();
        $expire = $issuedAt + 3600; //1 Stunde gültig

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => 'archiva', //unser zweiter Development Name (Danke an @EMMA_HAT_KEIN_GITUB)
            'sub' => $user_id
        ];

        return JWT::encode($payload, $this->jwtkey, 'HS256');
    }

    public function validate_JWT(string $token): bool {
        try {
            $payload = (array) JWT::decode($token, new Key($this->jwtkey, 'HS256'));
            if ($payload['exp'] < time()) {
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getUserIdFromJWT(string $token): ?int {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtkey, 'HS256'));
            return $decoded->sub ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
