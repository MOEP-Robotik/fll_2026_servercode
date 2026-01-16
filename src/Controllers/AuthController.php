<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;
use Database\AccountDatabase;

class AuthController{
    public function login_request(string $email, string $password): string | false { #returnt entweder false oder den jwt_token
        $accountdb = new AccountDatabase();
        $account = $accountdb->getByEmail($email);
        $passfromDB = $account['passhash']; #von Datenbank nehmen
        $user_id = $account['id']; #von Datenbank nehmen
        if (password_verify($password, $passfromDB)){
            $auth = new Auth();
            $jwt_token = $auth->generate_jwt($user_id);
            return $jwt_token;
        } else {
            return false;
        }
    }
    public function validate_login(string $jwt_token): bool {
        $auth = new Auth();
        return $auth->validate_JWT($jwt_token);
    }
}