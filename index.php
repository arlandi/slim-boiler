<?php
/**
*
* Require composer autoloader
*/

require __DIR__ . '/vendor/autoload.php';

/**
* Require config file
* @return Array config values
*/

$config = require __DIR__ . '/config/config.php';

/**
* Create an instance of Slim with custom view
* and set the configurations from config file
*/

$slim = new Slim\Slim(array('view' => new SlimBoiler\View(),'mode' => 'production'));

$app = new SlimBoiler\App($slim, $config);

$app->run();
