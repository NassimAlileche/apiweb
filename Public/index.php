<?php
//session_start();

$basePath = str_replace("Public/index.php", "", $_SERVER["SCRIPT_FILENAME"]);

define('APIWEB_FS_ROOT', $basePath);
define('NAMESPACE_DOMAIN', 'apiweb\\');

// Chargement de l'autoloader
require_once($basePath . "Library/Loader/Autoloader.php");
$autoload = \apiweb\Library\Loader\Autoloader::getInstance();
$autoload::setBasePath($basePath);

$server = new \apiweb\Application\Core\RestServer();
$server->handle();

