<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserStorage
 *
 * @author User
 */
class UserStorage implements OAuth2\Storage\UserCredentialsInterface {
    
    public function checkUserCredentials($username, $password) {
        $credentials = array(
            'email' => $username,
            'password' => $password
        );
        return Auth::validate($credentials);
    }

    public function getUserDetails($username) {
        $user = User::whereEmail($username)->first();
        return array(
            "user_id"=>$user->id
        );
    }
}
