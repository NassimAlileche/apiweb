<?php

//session_start();

$basePath = str_replace("Public", "", dirname(__FILE__));
define('FILEROOT', $basePath);

// Chargement de l'autoloader
require_once(str_replace("Public", "Library/Loader/Autoloader.php", dirname(__FILE__)));
$autoload = \apiweb\Library\Loader\Autoloader::getInstance();
$autoload::setBasePath($basePath);

$server = new \apiweb\Application\Core\RestServer();
$server->handle();