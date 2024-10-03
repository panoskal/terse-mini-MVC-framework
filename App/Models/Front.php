<?php

/**
 * User model
 *
 * PHP version 7.4
 */

namespace App\Models;

use \App\Models\Helpers\MySQL;
use \App\Models\Helpers\Validation\Validator as Validator;
use \App\Models\Helpers\Password as Password;
use \App\Models\Helpers\Recaptcha;

class User
{
    private $dbConn;

    public function __construct()
    {
        $this->dbConn = new MySQL();
    }


    public function userLogin(array $data)
    {
        global $session;

        $success = true;
        $errors = [];

        if (empty($data['multi_id_number'])) {
            $errors[] = 'Το πεδίο "Α.Μ.Κ.Α. / Α.Φ.Μ." δεν μπορεί να είναι κενό';
            $success = false;
        }

        if (empty($data['mobile'])) {
            $errors[] = 'Το πεδίο "Κινητό" δεν μπορεί να είναι κενό';
            $success = false;
        }

        if ($success) {
            if (!empty($data['multi_id_number'])) {
                $request = $this->dbConn
                    ->selectQuery([], 'applications')
                    ->whereQuery('where', 'social_security_reg_number', 'equal', [$data['multi_id_number']], 'open')
                    ->whereQuery('or', 'tax_identification_number', 'equal', [$data['multi_id_number']], 'close')
                    ->whereQuery('and', 'mobile', 'equal', [$data['mobile']])
                    ->limitQuery(1)
                    ->getObj('\App\Models\User');

                if (empty($request)) {
                    $errors[] = 'Δεν βρέθηκε ο χρήστης με τα στοιχεία που εισάγατε';
                    $success = false;
                }
            }
        }

        $recaptcha = new Recaptcha();
        $recaptchaResult = $recaptcha->verifyRecaptcha3($data['recaptcha_response']);

        if ($recaptchaResult->result["success"] != '1') {
            $errors[] = "Παρουσιάστηκε πρόβλημα στην επαλήθευση recaptcha";
        }

        if (!empty($errors)) {
            $errorsJSON = json_encode($errors, JSON_UNESCAPED_UNICODE);
            setcookie("loginErrors", $errorsJSON, time() + 3600, "/".ROOT_FOLDER);
            return false;
        } else {
            $session->login($request[0], true);
            return true;
        }
    }


    public function userAuth($user)
    {
        global $session;

        $loginReturn = array();
        $loginSuccess = true;

        $this->getDB();

        $query = "SELECT * FROM users WHERE username=:username LIMIT 1";
        $result_array = $this->getObj($query, "\App\Models\User", array(':username' => $user['username']));

        $foundUser = !empty($result_array) ? array_shift($result_array) : false;

        if ($foundUser) {
            if (InputProcess::passVerify($user['password'], $foundUser->password)) {
                if ($foundUser->active == 0) {
                    $loginReturn['error']['inactive'] = "Your account is inactive. Please contact the administrator";
                    $loginSuccess = false;
                }
            } else {
                $loginReturn['error']['passwordErr'] = "Wrong password";
                $loginSuccess = false;
            }
        } else {
            $loginReturn['error']['usernameErr'] = "User not found";
            $loginSuccess = false;
        }

        if ($loginSuccess) {
            $login_user = new UserGroup();
            $user_group = $login_user->getUserGroupById($foundUser->user_group_id);
            $session->login($foundUser, $user_group);
            return true;
        } else {
            $session->logout();
            $loginErrorMessage = json_encode($loginReturn['error'], JSON_UNESCAPED_UNICODE);
            setcookie("userAuthFormError", $loginErrorMessage);
            return false;
        }
    }
}
