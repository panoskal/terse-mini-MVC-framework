<?php

/**
 * Users controller
 *
 * PHP version 7.4
 */

namespace App\Controllers\Admin;

class Users extends \Core\Controller {

    /**
     * Logout user
     *
     * @return void
     */
    public function logoutAction()
    {

        global $session;

        $session->logout();

        parent::redirect();

    }

}
