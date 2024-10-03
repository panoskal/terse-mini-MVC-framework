<?php
/**
 * Home controller
 *
 * PHP version 7.4
 */

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\User;
use \App\Models\Form;

class Home extends \Core\Controller {

	private $userModel;

    /**
     * Before filter
     *
     * @return void
     */
    protected function instantiate() {
		$this->userModel = new User();
    }

    /**
     * After filter
     *
     * @return void
     */
    protected function after() {
    }

    /**
     * Show the login or home page
     *
     * @return void
     */
    public function indexAction() {

        global $session;

        $args = [];

        $cookiePath = $_SERVER['REQUEST_URI'];

        $args['errors'] = !empty($_COOKIE["loginErrors"]) ? json_decode($_COOKIE["loginErrors"], true) : null;
        $args['page'] = 'login';
        $args['template']['name'] = "Home";
        // $args['recaptcha']['action'] = RECAPTCHA_ACTION;
        // $args['recaptcha']['sitekey'] = RECAPTCHA_SITE_KEY;

        if (!$session->isLoggedIn()) {
            View::renderTemplate('Admin/Pages/login.html', $args);
        } else {
            // redirect to admin panel
        }
        

        if (!empty($_COOKIE["loginErrors"])) {
            setcookie("loginErrors", "", time() - 3600, $cookiePath);
        }
    }

    /**
     * Login action
     *
     * @return void
     */
    public function loginAction() {

        global $session;

		$this->user = new User();

        $loginSuccess = false;

        $loginSuccess = $this->user->login($_POST);

		parent::redirect();

    }


    public function logoutAction()
    {
        global $session;

        $session->logout();
    }
}
