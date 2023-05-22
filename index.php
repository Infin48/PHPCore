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

$start = microtime(true);

// Root
define('ROOT', rtrim($_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']), '/'));
mb_internal_encoding('UTF-8');
ob_start();

if (isset($_COOKIE['PHPSESSID']))
{
    session_write_close();
    session_id($_COOKIE['PHPSESSID']);
    session_start();
} else {
    session_start();
}

error_reporting(E_ERROR | E_PARSE);
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

require ROOT . '/Includes/Function.php';

if (is_dir(ROOT . '/Install/'))
{
    define('INCLUDES', '/Install/Includes/Object');
} else
{
    define('INCLUDES', '/Includes/Object');
}

spl_autoload_register(function ($class)
{
    $_path = explode('\\', $class);
    if (count($_path) <= 1)
    {
        return;
    }

    switch ($_path[0])
    {
        case 'App':
            array_shift($_path);
            $path = INCLUDES;
        break;

        case 'Plugin':
            array_shift($_path);
            $path = '/Plugins/' . array_shift($_path) . '/Object';
        break;

        case 'Style':
            array_shift($_path);
            $path = '/Styles/' . array_shift($_path) . '/Object';
        break;

        default:
            throw new \App\Exception\System('Class "' . $class . '" does not exist!');
    }

    if ($_path[0] == 'Table')
    {
        $path .= '/Model/Database';
    }

    $path .= '/' . implode('/', $_path) . '.';


    $path .= match ($_path[0])
    {
        'Page' => 'page.php',
        'Table' => 'table.php',
        'Style' => 'style.php',
        'Model' => 'model.php',
        'Plugin' => 'plugin.php',
        'Exception' => 'exception.php',
        'Visualization' => 'visualization.php',
        default => throw new \App\Exception\Exception('Class "' . $class . '" has unsupported format!')
    };
    
    if (file_exists(ROOT . $path) === false)
    {
        match ($_path[0])
        {
            'Page' => throw new \App\Exception\System('Hledaná stránka \'' . $path . '\' neexistuje!'),
            'Table' => throw new \App\Exception\System('Hledaná tabulka \'' . $path . '\' neexistuje!'),
            'Style' => throw new \App\Exception\System('Hledaná styl \'' . $path . '\' neexistuje!'),
            'Model' => throw new \App\Exception\System('Hledaný model \'' . $path . '\' neexistuje!'),
            'Plugin' => throw new \App\Exception\System('Hledaný plugin \'' . $path . '\' neexistuje!'),
            'Exception' => throw new \App\Exception\System('Hledaná vyjímka \'' . $path . '\' neexistuje!'),
            'Visualization' => throw new \App\Exception\System('Hledaný vizualizátor \'' . $path . '\' neexistuje!')
        };
    }
    

    require_once(ROOT . $path);
});

set_exception_handler(function ($exception)
{
    throw new \App\Exception\System($exception);
});

require ROOT . '/Includes/Constants.php';

if (is_dir(ROOT . '/Install/'))
{
    if (!is_writeable(ROOT . '/Install/Includes/Settings.json') or !is_readable(ROOT . '/Install/Includes/Settings.json'))
    {
        throw new \App\Exception\System('Apliakce vyžaduje oprávnění číst a zapisovat do souboru "/Install/Includes/Settings.json"!');
    }

    $db = new \App\Model\Database();
    $data = new \App\Model\Data();

    $router = new \App\Page\Router(
        db: $db,
        data: $data
    );
    $router->body( $data, $db );
    
    exit();
}

$url = urldecode($_SERVER['REQUEST_URI']);
if (str_starts_with(strtolower($url), '/ajax/') or str_starts_with(strtolower($url), '/admin/ajax/') or preg_match('#^/plugin/(.*?)/ajax/#', strtolower($url)))
{
    define('AJAX', true);
}
$data = new \App\Model\Data();
$db = new \App\Model\Database\Query( $data );

// And buildes
$build                = new \stdClass();
$build->url           = new \App\Model\Build\Url();
$build->date          = new \App\Model\Build\Date( language: $data->get('inst.language') );
$build->user          = new \App\Model\Build\User( language: $data->get('inst.language'), system: $data->get('inst.system'));

// Inicialise URL
$_url = new \App\Model\Url( $db->select('app.settings.URLDefault()'), $db->select('app.settings.URLHidden()') );
$_url->parseURL();

if (str_starts_with(strtolower($url), '/admin/'))
{

    $router = new \App\Page\Admin\Router(
        db: $db,
        url: $_url,
        data: $data,
        build: $build
    );
    $router->body( $data, $db );

    \App\Model\Database\Database::destroy();

    exit();
}

$router = new \App\Page\Router(
    db: $db,
    url: $_url,
    data: $data,
    build: $build
);
$router->body( $data, $db );

\App\Model\Database\Database::destroy();

exit();