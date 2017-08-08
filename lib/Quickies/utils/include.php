<?php
namespace Iekadou\Quickies;
require_once("instantiate.php");

date_default_timezone_set('Europe/Berlin');

$RENDERING_START = microtime(true);
// get request method
if (isset($_POST['_method']) && ($_POST['_method'] == 'GET' || $_POST['_method'] == 'POST' || $_POST['_method'] == 'PUT' || $_POST['_method'] == 'DELETE')) {
    define('REQUEST_METHOD',  $_POST['_method']);
} else {
    define('REQUEST_METHOD', "GET");
}

session_start();
define('Lare', true);

// configs
include(PATH."config/webapp.php");
// includes
require_once(PATH."vendor/autoload.php");
// require_once all Models and Classes here
foreach (glob(PATH."classes/*.php") as $filename) {
    require_once($filename);
}

Globals::set_var('Account', new Account());
Globals::set_var('SITE_NAME', SITE_NAME);
Globals::set_var('DOMAIN', DOMAIN);
Globals::set_var('LANG', Translation::$activateLanguage);

$INIT_LOADED = true;
