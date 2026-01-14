<?php
namespace Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use Core\Auth;

class AuthController{
    public function login_request(string $user, string $password) {
        $passfromDB = ;
        if (password_verify($password, $passfromDB)){
            return true;
        } else {
            return false;
        }
    }
}