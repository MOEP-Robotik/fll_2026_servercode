<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;
use Database\AccountDatabase;
use Core\Request;
use Core\Response;
use Models\Account;
use Models\UserInfo;

class AuthController {
    public function authenticate(Request $request): void {
        if (!$request->post() && !$request->get()){
            Response::json(['message' => 'Wrong Method (Try POST or GET)'], 405);
            return;
        }
        $data = $request->json();
        $header = $request->header();
        switch ($request->path()){
            case "/api/auth/login":
                $this->loginRequest($data['email'], $data['password']);
                return;
            case "/api/auth/validate":
                $this->validateToken($header['Authorization']);
                return;
            case "/api/auth/register":
                $this->registerRequest($data['email'], $data['password'], $data['vorname'], $data['nachname'], $data['plz'], $data['telefonnummer']);
                return;
            case "/api/auth/userinfo":
                $this->getUserInfo($header['Authorization']);
                return;
            case "/api/auth/requestguest":
                $this->requestGuest();
                return;
            default:
                Response::json(['message' => "Resource not found"], 404);
                return;
        }
    }

    public function loginRequest(string $email, string $password): void {
        if ($email == "Gast") {
            Response::json(['message' => "Login as guest not allowed"], 403);
            return;
        }

        $accountdb = new AccountDatabase();
        $account = $accountdb->getByEmail($email);
        if (!$account) {
            Response::json(['message' => "User not found"], 404);
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

        $auth = new Auth();
        $jwt_token = $auth->generate_jwt($newId);

        Response::json(['jwt_token' => $jwt_token]);
    }

    public function getUserInfo(string $token): UserInfo {
        $auth = new Auth();
        $valid = $auth->validate_JWT($token);
        if (!$valid) {
            Response::json(['message'=> 'JWT Token invalid'], 401);
            return new UserInfo();
        }

        $user_id = $auth->getUserIdFromJWT($token);

        $accountdb = new AccountDatabase();
        $user = $accountdb->getById($user_id);

        $userinfo = new UserInfo();
        $userinfo->vorname = $user->vorname;
        $userinfo->nachname = $user->nachname;
        $userinfo->plz = $user->plz;
        $userinfo->email = $user->email;
        $userinfo->telefonnummer = $user->telefonnummer;
        $userinfo->funde = $user->funde;

        Response::json($userinfo);
        return $userinfo;
    }

    public function requestGuest(): string {
        $this->registerRequest("Gast", "Gast", "Gast", "Gast", 0, "Gast");
    }

    public function getUserId(string $token): int {
        $auth = new Auth();
        $valid = $auth->validate_JWT($token);
        if (!$valid) {
            Response::json(['message' => 'JWT Token invalid'], 401);
            return -1;
        }

        $userId = $auth->getUserIdFromJWT($token);
        return $userId;
    }
}