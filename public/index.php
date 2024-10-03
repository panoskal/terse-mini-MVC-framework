<?php
ob_start();

/**
 * Composer
 */
require '../vendor/autoload.php';
require '../Core/defines.php';
require '../Core/init.php';

define('UPLOADS_FOLDER', __DIR__);

/**
 * Twig
 */
//Twig_Autoloader::register();

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


$router = new Core\Router();


$router->add('admin', ['namespace' => 'Admin', 'controller' => 'Home', 'action' => 'index']);
$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);
$router->add('admin/{controller}/{cid:\d+}/{action}', ['namespace' => 'Admin']);
$router->add('admin/{controller}/{cid:\d+}/{action}/{aid:\d+}', ['namespace' => 'Admin']);

$router->add('user/{controller}/{action}', ['namespace' => 'User']);
$router->add('user/{controller}/{cid:\d+}/{action}', ['namespace' => 'User']);
$router->add('user/{controller}/{cid:\d+}/{action}/{aid:\d+}', ['namespace' => 'User']);

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}/{action}');
$router->add('{controller}/{cid:\d+}/{action}');
$router->add('{controller}/{cid:\w+}/{action}');
$router->add('{controller}/{cid:\d+}/{action}/{aid:\d+}');



$queryString = defined('ROOT_FOLDER') ? str_replace("/" . ROOT_FOLDER . "/", "", $_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'];

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['QUERY_STRING']);

ob_end_flush();
