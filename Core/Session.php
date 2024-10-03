<?php

namespace Core;

class Session {

    private $loggedIn = false;
    private $isActive = false;
    private $userLevel;
    public $fullname;
    public $id;

    function __construct()
    {
        if(!isset($_SESSION)) {
            session_start();
        }
        $this->checkLogin();
    }

    public function login($user, $simpleUser)
    {

		if ($user) {
			$this->id = $_SESSION['id'] = $user->id;
			$this->fullname = $_SESSION['fullname'] = $user->firstname . ' ' . $user->lastname;
            if ($simpleUser) {
                $this->userLevel = $_SESSION['user_level'] = 'kvdmsys_user_level';
                $this->isActive = $_SESSION['active'] = 1;
            } else {
                $this->userLevel = $_SESSION['user_level'] = 'kvdmsys_admin_level';
                    if (!empty($user->active)) {
    				$this->isActive = $_SESSION['active'] = 1;
    			} else {
    				$this->isActive = $_SESSION['active'] = 0;
    			}
            }
			$this->loggedIn = true;
			return true;
		} else {
			$this->loggedIn = false;
			return false;
		}
    }

    private function checkLogin()
    {
		if (isset($_SESSION['id']) && !empty($_SESSION['user_level'])) {
            if (($_SESSION['user_level'] === 'kvdmsys_admin_level' || $_SESSION['user_level'] === 'kvdmsys_user_level') && !empty($_SESSION['active'])) {
                $this->loggedIn = true;
                $this->id = $_SESSION['id'];
                $this->userLevel = $_SESSION['user_level'];
                $this->isActive = $_SESSION['active'];
                if (isset($_SESSION['fullname'])) {
                    $this->fullname = $_SESSION['fullname'];
                }
            } else {
                unset($_SESSION['id']);
                unset($_SESSION['user_level']);
                unset($this->id);
                $this->loggedIn = false;
            }
        } else {
            unset($this->id);
            $this->loggedIn = false;
        }
    }

    public function isLoggedIn()
    {
        return $this->loggedIn;
    }

    public function isActive()
    {
        return $this->isActive;
    }

    public function getUserLevel()
    {
        return $this->userLevel;
    }

    public function updateSession()
    {
        foreach($_SESSION as $key=>$value) {
            $this->$key = $value;
        }
    }

    public function logout()
    {
        unset($_SESSION['id']);
        unset($_SESSION['fullname']);
        unset($this->id);
        unset($this->fullname);
        $this->loggedIn = false;
        session_destroy();
    }

}
