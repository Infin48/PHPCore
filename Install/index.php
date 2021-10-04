<?php

/**
 * This file is part of the PHPCore forum software
 * 
 * Made by InfinCZ 
 * @link https://github.com/Infin48
 *
 * @copyright (c) PHPCore Limited https://phpcore.cz
 * @license GNU General Public License, version 3 (GPL-3.0)
 */

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// DEFINE ROOT
$ex = explode('/', rtrim($_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']), '/'));
array_pop($ex);
define('ROOT', implode('/', $ex));

// SET UTF-8
mb_internal_encoding('UTF-8');

// SET BASIC SETTINGS
ob_start();
session_start();

error_reporting(E_ALL ^ E_NOTICE);

ini_set('memory_limit', '256M');

ini_set('display_errors', 1);
ini_set('auto_start', 0);

ini_set('opcache.enable', 0);
ini_set('opcache.enable_cli', 0);

@ini_set('session.cookie_secure', 1);
@ini_set('session.use_strict_mode', 1);
@ini_set('session.use_only_cookies ', 1);
@ini_set('session.use_trans_sid ', 0);
@ini_set('session.cookie_httponly', 1);
@ini_set('session.use_cookies ', 1);
@ini_set('session.cookie_domain ', $_SERVER['SERVER_NAME']);

spl_autoload_register(function ($class) {
    
    $path = ROOT . '/Install/Includes/' . implode('/', $_path = explode('\\', $class)) . '.';

    $path .= match ($_path[0]) {
        'Page' => 'page.php',
        'Model' => 'model.php',
        'Style' => 'style.php',
        'Process' => 'process.php',
        'Exception' => 'exception.php'
    };

    if (file_exists($path) === false) {

        match ($_path[0]) {
            'Page' => throw new \Exception\System('Hledaná stránka \'' . $path . '\' neexistuje!'),
            'Model' => throw new \Exception\System('Hledaný model \'' . $path . '\' neexistuje!'),
            'Style' => throw new \Exception\System('Hledaná styl \'' . $path . '\' neexistuje!'),
            'Process' => throw new \Exception\System('Hledaný proces \'' . $path . '\' neexistuje!'),
            'Exception' => throw new \Exception\System('Hledaná vyjímka \'' . $path . '\' neexistuje!')
        };

    }
    require_once($path);
});

function refresh()
{
    header('Location:/Install/');
}

// IF WILL BE CAUGHT ANY EXCEPTION SHOW IT
set_exception_handler(function ($exception) {
    throw new \Exception\System($exception);
});

require ROOT . '/Includes/Function.php';
require ROOT . '/Includes/Constants.php';

$router = new Page\Router();
$router->body();
\Model\Database::destroy();
exit();