<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;

class AuthController{
    public function login_request(string $user, string $password) { #returnt entweder false oder den jwt_token
        $passfromDB = ; #von Datenbank nehmen
        $user_id = ; #von Datenbank nehmen
        if (password_verify($password, $passfromDB)){
            $auth = new Auth();
            $jwt_token = $auth->generate_jwt($user_id)
            return $jwt_token;
        } else {
            return false;
        }
    }
}