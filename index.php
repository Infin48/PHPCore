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

// ROOT
define('ROOT', rtrim($_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']), '/'));

if (is_dir(ROOT . '/Install/')) {
    header('Location: ' . '/Install/');
    exit();
}

mb_internal_encoding('UTF-8');

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

require ROOT . '/Includes/Function.php';

spl_autoload_register(function ($class) {

    $_path = explode('\\', $class);
    $path = ROOT . '/Includes/Object/' . implode('/', $_path) . '.';

    if ($_path[1] === 'Plugin' and isset($_path[2])) {

        $_path = array_values($_path);

        $path = ROOT . '/Plugins/' . $_path[2] . '/Object/' . $_path[0] . '/' . implode('/' , array_slice($_path, 3)) . '.';

        $path .= match ($_path[0]) {
            'Page' => 'page.php',
            'Model' => 'model.php',
            'Block' => 'block.php',
            'Process' => 'process.php'
        };

    } else {

        $path .= match ($_path[0]) {
            'Page' => 'page.php',
            'Style' => 'style.php',
            'Model' => 'model.php',
            'Block' => 'block.php',
            'Plugin' => 'plugin.php',
            'Process' => 'process.php',
            'Exception' => 'exception.php',
            'Visualization' => 'visualization.php'
        };
    }

    if (file_exists($path) === false) {

        match ($_path[0]) {
            'Page' => throw new \Exception\System('Hledan?? str??nka \'' . $path . '\' neexistuje!'),
            'Style' => throw new \Exception\System('Hledan?? styl \'' . $path . '\' neexistuje!'),
            'Model' => throw new \Exception\System('Hledan?? model \'' . $path . '\' neexistuje!'),
            'Block' => throw new \Exception\System('Hledan?? blok \'' . $path . '\' neexistuje!'),
            'Plugin' => throw new \Exception\System('Hledan?? plugin \'' . $path . '\' neexistuje!'),
            'Process' => throw new \Exception\System('Hledan?? proces \'' . $path . '\' neexistuje!'),
            'Exception' => throw new \Exception\System('Hledan?? vyj??mka \'' . $path . '\' neexistuje!'),
            'Visualization' => throw new \Exception\System('Hledan?? vizualiz??tor \'' . $path . '\' neexistuje!')
        };
    }

    require_once($path);
});

set_exception_handler(function ($exception) {
    throw new \Exception\System($exception);
});

require ROOT . '/Includes/Constants.php';

$url = urldecode($_SERVER['REQUEST_URI']);

if (str_starts_with(strtolower($url), '/admin/')) {
    $router = new Page\Admin\Router();
} else {
    $router = new Page\Router();
}
$router->body();
Model\Database\Database::destroy();

exit();