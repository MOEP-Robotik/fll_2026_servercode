<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;
use Database\AccountDatabase;
use Core\Request;
use Core\Response;
use Models\Account;

class AuthController {
    public function authenticate(Request $request): void {
        if (!$request->post()){
            Response::json(['message' => 'Wrong Method (Try POST)'], 405);
            return;
        }
        $data = $request->json();
        switch ($request->path()){
            case "/api/auth/login":
                $this->loginRequest($data['email'], $data['password']);
                return;
            case "/api/auth/validate":
                $this->validateToken($data['jwt_token']);
                return;
            case "/api/auth/register":
                $this->registerRequest($data['email'], $data['password'], $data['vorname'], $data['nachname'], $data['plz'], $data['telefonnummer']);
                return;
            default:
                Response::json(['message' => "Resource not found"], 404);
                return;
        }
    }

    public function loginRequest(string $email, string $password): void {
        $accountdb = new AccountDatabase();
        $account = $accountdb->getByEmail($email);
        if (!$account) {
            Response::json(['message' => 'User not found'], 404);
            return;
        }
        $passfromDB = $account->passhash;
        $user_id = $account->id;
        if (password_verify($password, $passfromDB)){
            $auth = new Auth();
            $jwt_token = $auth->generate_jwt($user_id);
            Response::json(['jwt_token' => $jwt_token], 200);
        } else {
            Response::json(['message' => "Invalid password"], 401);
        }
    }

    public function validateToken(string $jwt_token): void {
        $auth = new Auth();
        $valid = $auth->validate_JWT($jwt_token);
        Response::json(['valid' => $valid]);
    }

    public function registerRequest(string $email, string $password, string $vorname, string $nachname, int $plz, string $telefonnummer) {
        if (empty($email) || empty($password) || empty($vorname) || empty($nachname) || empty($telefonnummer)) {
            Response::json(['message' => 'Required fields are empty'], 400);
            return;
        }

        $accountdb = new AccountDatabase();
        $account = $accountdb->getByEmail($email);
        if ($account) {
            Response::json(['message' => 'User already exists'], 409);
            return;
        }

        $passhash = password_hash($password, PASSWORD_DEFAULT);

        $account = new Account();
        $account->email = $email;
        $account->passhash = $passhash;
        $account->vorname = $vorname;
        $account->nachname = $nachname;
        $account->plz = $plz;
        $account->telefonnummer = $telefonnummer;
        $account->funde = [];

        $newId = $accountdb->create($account);
        if (!$newId) {
            Response::json(['message' => 'Registration failed while saving user'], 500);
            return;
        }

        Response::json(['id' => $newId]);
    }
}