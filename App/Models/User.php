<?php

/**
 * User model
 *
 * PHP version 7.4
 */

namespace App\Models;

use Core\MySql;
use \App\Models\Tables\Table;
use \App\Models\Helpers\Validation\Validator as Validator;
use \App\Models\Helpers\Password as Password;
use \App\Models\Helpers\Recaptcha;

class User extends MySQL
{


    public function testValidation()
    {

        $request = [
            'name'              => '',
            'email'             => 'kalogirou.pa@gmail.com',
            'password'          => '1234qwer',
            'confirm_password'  => '1234qwer',
            'phone'             => '6911111111'
        ];

        Validator::validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:10'
        ],[
            'name.required' => 'The name field is required',
            'email.required' => 'The email field is required',
            'email.email' => 'The email field must be valid email',
            'password.min' => 'The password must be at least 10 characters'
        ]);

    }



    public function login($data) {

        global $session;

        $cookiePath = $_SERVER['REQUEST_URI'];
        $success = true;

        $user = Table::process('admins')->getTableRowByKeys([
            ['name' => 'username', 'value' => $data['username']],
            ['name' => 'email', 'value' => $data['username']]
        ]);

        if (empty($user) || empty($user->password)) {
            setcookie("loginErrors", json_encode(['Inputs not valid'], JSON_UNESCAPED_UNICODE), time() + 3600, $cookiePath);
            return false;
        }

        $errors = Validator::validate($data, [
            'password' => 'password:' . $user->password
        ],[
            'password.password' => 'Wrong username/password'
        ]);

        if (!empty($errors)) {
            setcookie("loginErrors", json_encode(['Wrong username/password'], JSON_UNESCAPED_UNICODE), time() + 3600, $cookiePath);
            return false;
        }

        $session->login($user, false);

        return true;

	}

}
