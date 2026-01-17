<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;
use Database\AccountDatabase;
use Core\Request;
use Core\Response;

class AuthController {
    public function authenticate(Request $request): void {
        if ($request->post()){
            switch ($request->path()){
                case "/api/auth/login":
                    $data = $request->json();
                    $this->loginRequest($data['email'], $data['password']);
                    return;
                case "/api/auth/validate":
                    $data = $request->json();
                    $this->validateToken($data['jwt_token']);
                    return;
                default:
                    Response::json(['message' => "Ressource not found"], 404);
                    return;
            }
        } else {
            Response::json(['message' => 'Wrong Method (Try POST)'], 405);
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
}