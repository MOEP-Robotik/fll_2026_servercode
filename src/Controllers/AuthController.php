<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;
use Database\AccountDatabase;
use Core\Request;
use Core\Response;

class AuthController{
    public function authenticate(Request $request): void{
        if ($request->post()){
            switch ($request->path()){
                case "/api/auth/login":
                    $data = $request->json();
                    $this->login_request($data['email'], $data['password']);
                    return;
                case "/api/auth/validate":
                    $data = $request->json();
                    $this->validate_token($data['jwt_token']);
                    return;
                default:
                    Response::json(['message' => "Ressource not found"], 404);
                    return;
            }
        } else {
            Response::json(['message' => 'Wrong Method (Try POST)'], 405);
            return;
        }
    }

    public function login_request(string $email, string $password): void {
        $accountdb = new AccountDatabase();
        $account = $accountdb->getByEmail($email);
        $passfromDB = $account['passhash'];
        $user_id = $account['id'];
        if (password_verify($password, $passfromDB)){
            $auth = new Auth();
            $jwt_token = $auth->generate_jwt($user_id);
            Response::json(['jwt_token' => $jwt_token], 200);
            return;
        } else {
            Response::json(['jwt_token' => null], 401);
            return;
        }
    }

    public function validate_token(string $jwt_token): void {
        $auth = new Auth();
        $valid = $auth->validate_JWT($jwt_token);
        Response::json(['valid' => $valid]);
        return;
    }
}