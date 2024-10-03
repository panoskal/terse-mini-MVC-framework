<?php

namespace Core;

/**
 * Base controller
 *
 * PHP version 7.4
 */
abstract class Controller {

	/**
	 * Parameters from the matched route
	 * @var array
	 */
	protected $routeParams = [];

	/**
	 * Class constructor
	 *
	 * @param array $routeParams  Parameters from the route
	 *
	 * @return void
	 */
	public function __construct($routeParams) {
		$this->routeParams = $routeParams;
	}

	/**
	 * Magic method called when a non-existent or inaccessible method is
	 * called on an object of this class. Used to execute before and after
	 * filter methods on action methods. Action methods need to be named
	 * with an "Action" suffix, e.g. indexAction, showAction etc.
	 *
	 * @param string $name  Method name
	 * @param array $args Arguments passed to the method
	 *
	 * @return void
	 */
    public function __call($name, $args)
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            } else {
                $this->redirect();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

	/**
	 * Before filter - called before an action method.
	 *
	 * @return void
	 */
	protected function before()
    {
        global $session;

        if (!empty($this->routeParams['namespace'])) {
            if ($this->routeParams['namespace'] === 'User') {
                if (!$session->isLoggedIn() || $session->getUserLevel() !== 'kvdmsys_user_level') {
                    return false;
                }
            } else if ($this->routeParams['namespace'] === 'Admin') {
                if (!$session->isLoggedIn() || $session->getUserLevel() !== 'kvdmsys_admin_level' || !$session->isActive()) {
                    if ($this->routeParams['controller'] !== 'Home') {
                        return false;
                    }
                }
            }
        }
        return true;
	}

	/**
	 * After filter - called after an action method.
	 *
	 * @return void
	 */
	protected function after() {
	}


	public function redirect($route = array(), $args = array()) {
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$domainName = $_SERVER['HTTP_HOST'];

		if (!empty($route) && is_array($route)) {
			$controller = !empty($route['controller']) ? $route['controller'] . "/" : "";
			$controller = defined('ROOT_FOLDER') ? ROOT_FOLDER . $controller : $controller;
			$cid = !empty($route['cid']) ? $route['cid'] . "/" : "";
			$action = !empty($route['action']) ? $route['action'] . "/" : "";
			$aid = !empty($route['aid']) ? $route['aid'] : "";

			$location = rtrim($protocol . $domainName . "/" . $controller . $cid . $action . $aid, '/');

		} else {
            $location = defined('ROOT_FOLDER') ? rtrim($protocol . $domainName . "/" . ROOT_FOLDER, '/') : '/';
            $location = !empty($route) ? $location . '/' . $route : $location;
        }

		header("Location: " . $location);
		exit;
	}
}
