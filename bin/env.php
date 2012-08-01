<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../app'));

if (!defined('APPLICATION_ENV'))
{
    // check for app environment setting
    if ($i = array_search('-e', $_SERVER['argv']))
    {
        define('APPLICATION_ENV', $_SERVER['argv'][$i+1]);
    }
    else
    {
        define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'production');
    }
}

if (($idx = array_search('-d', $_SERVER['argv'])) && ($inc = $_SERVER['argv'][$idx+1]))
{
    set_include_path(implode(PATH_SEPARATOR, array(
        $inc,
        get_include_path(),
    )));
}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../lib'),
    get_include_path(),
)));

/** Load Const */
require_once APPLICATION_PATH . '/consts/app.php';

/** Use autoloader */
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

// Create application, bootstrap, and run
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        'config' => array(
            APPLICATION_PATH . '/configs/application.ini',
            APPLICATION_PATH . '/configs/application.cli.ini',
        )
    )
);

Zyon::launch();

$application->bootstrap()
    ->getBootstrap()->getResource('frontController')
    ->setParam('bootstrap', $application->getBootstrap())
    ->setRequest('Zend_Controller_Request_Simple');
