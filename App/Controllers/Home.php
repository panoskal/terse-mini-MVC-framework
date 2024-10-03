<?php
/**
 * Home controller
 *
 * PHP version 7.4
 */

namespace App\Controllers;

use \Core\View;
use \App\Models\Front;
use \App\Models\Test;

class Home extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function instantiate()
    {}

    /**
     * After filter
     *
     * @return void
     */
    protected function after()
    {}


    /**
     * Home page
     *
     * @return void
     */
    public function indexAction()
    {

        // $test = new Test();

        // $test->testValidation();

        // $args['page'] = 'home';
        // $args['welcome'] = 'Welcome to home page';

        $args = [];

        View::renderTemplate('Pages/home.html', $args);

    }


    public function loginAction() {

        // global $session;

        // $this->user = new User();

        // $loginSuccess = false;

        // $loginSuccess = $this->user->userLogin($_POST);

		// if (!$loginSuccess || !$session->id) {
		// 	parent::redirect('/');
		// }

		// parent::redirect([
        //     'controller'=> 'user/users-infos',
        //     'cid' => $session->id,
        //     'action'=>'view'
        // ]);

    }

}
