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

namespace Page;

use Block\Plugin;

use Model\Language;
use Model\Template;

use Visualization\Visualization;

/**
 * Page
 */
abstract class Page
{
    /**
     * @var string $definedURL Stored page or folder pre-defined redirect url
     */
    private static string $definedURL = '';
    
    /**
     * @var object $page Page class
     */
    protected object $page;

    /**
     * @var \Model\Url $url Url
     */
    protected \Model\Url $url;

    /**
     * @var \Model\Data $data Data
     */
    protected \Model\Data $data;
    
    /**
     * @var \Model\User $user User
     */
    protected \Model\User $user;

    /**
     * @var \Plugin\Plugin $plugin Plugin
     */
    protected \Plugin\Plugin $plugin;
    
    /**
     * @var \Model\Build\Build $build Build
     */
    protected \Model\Build\Build $build;
    
    /**
     * @var \Process\Process $process Process
     */
    protected \Process\Process $process;
    
    /**
     * @var \Model\Language $language Language
     */
    protected \Model\Language $language;

    /**
     * @var \Model\Template $template Template
     */
    protected \Model\Template $template;

    /**
     * @var \Model\System $system System
     */
    protected \Model\System $system;

    /**
     * @var \Visualization\Navbar\Navbar $navbar Navbar
     */
    protected \Visualization\Navbar\Navbar $navbar;

    /**
     * @var array $parsedURL Parsed URL
     */
    protected static array $parsedURL = [];

    /**
     * @var int $numberOfIDs Number of required URL IDs
     */
    protected static int $numberOfIDs = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (isset($this->url)) {
            $this->url->shift();
        }
    }

    /**
     * Loads plugins
     * 
     * @return void
     */
    protected function loadPlugins()
    {
        $this->data->data['plugins'] = [];

        $plugin = new Plugin();
        foreach ($plugin->getAll() as $item) {

            // ADD PLUGIN TO LIST
            array_push($this->data->data['plugins'], $item['plugin_name_folder']);

            // INITIALISES EVERY PLUGIN
            $ini = '/Plugins/' . $item['plugin_name_folder'] . '/Ini.plugin.php';
            if (file_exists(ROOT . $ini)) {
                require ROOT . $ini;
            }
        }
    }
    
    /**
     * Initialise page
     *
     * @return void
     */
    protected function ini()
    {
        $pageClass = array_values(array_filter(explode('\\', get_class($this))));

        foreach (['Page', 'Index', 'Router'] as $item) {

            if (in_array($item, $pageClass)) {
                unset($pageClass[array_search($item, $pageClass)]);
            }
        }
        
        if (in_array($pageClass[array_key_last($pageClass)] ?? '', ['Edit', 'Add'])) {
            array_pop($pageClass);
        }

        $pageClass = array_values($pageClass);

        if (($pageClass[0] ?? '') === 'Plugin' and isset($pageClass[1])) {
            
            array_shift($pageClass);
            array_shift($pageClass);

            if ($pageClass[0] == 'Admin' or $pageClass[0] == 'Plugin') {
                array_shift($pageClass);
            }
        }

        $this->url->setURL(
            $this->url->build(
                mb_strtolower(implode('/', array_filter($pageClass))), true
            )
        );

        if (isset($this->settings['id'])) {
            
            self::$numberOfIDs++;

            if ($this->settings['id'] === string) {
                $this->url->getFirst() or $this->error();
                $this->url->addID($this->url->getFirst());
            }

            $this->url->getID(self::$numberOfIDs - 1) or $this->error();

            if ($this->settings['id'] === int) {
                if (!ctype_digit($this->url->getID(self::$numberOfIDs - 1))) {
                    $this->error();
                }
            }

            $this->url->setURL(
                $this->url->build(
                    $this->url->getID(self::$numberOfIDs - 1, false), true
                )
            );
        }

        $this->data->head['title'] = $this->language->get('L_TITLE')[get_class($this)] ?? $this->data->head['title'];
        
        $this->process->url($this->url->getURL());
        if (isset($this->settings)) {
            foreach (array_keys($this->settings) as $option) {
                switch ($option) {

                    case 'permission':
                        if ($this->user->perm->has($this->settings['permission']) === false) $this->error();
                    break;

                    case 'redirect':
                        $this->process->url(self::$definedURL = $this->settings['redirect']);
                    break;

                    case 'loggedOut':
                        if ($this->user->isLogged() === true) $this->error();
                    break;

                    case 'loggedIn':
                        if ($this->user->isLogged() === false) $this->error();
                    break;

                    case 'header':
                        $this->data->data([
                            'bigHeader' => true
                        ]);
                    break;

                    case 'editor':
                        $this->data->data([
                            'editor' => $this->settings['editor']
                        ]);
                    break;

                    case 'template':
                        $this->style->setTemplate($this->settings['template']);
                    break;

                    case 'notification':
                        $this->data->data([
                            'globalNotification' => (new \Block\Notification())->getAll()
                        ]);
                    break;
                }
            }
        }

    }
    
    /**
     * Builds page name according to url
     *
     * @return string
     */
    protected function build( string $path = null )
    {
        $_path = [];

        // DEFAULT PATH
        if (!$path) {
            $path = ROOT . '/Includes/Object/Page/';

            $namespace = explode('\\', get_class($this));
            $namespace = array_slice($namespace, 1, count($namespace) - 2);
            if (empty($namespace) === false) {
                $path .= implode('/', $_path = $namespace) . '/';
            }
        }

        while (true) {
            if (!empty($this->url->get())) {

                // IF DIR EXISTS
                if (is_dir($path . ucfirst($this->url->getFirst()) . '/')) {

                    array_push($_path, $shift = ucfirst($this->url->shift()));
                    $path .= $shift . '/';

                    if (file_exists($path . '/Router.page.php')) {
                        array_push($_path, 'Router');
                    break;
                    }

                    continue;
                }

                // IF PAGE EXISTS
                if (file_exists($path . ucfirst($this->url->getFirst()) . '.page.php')) {
                    array_push($_path, ucfirst($this->url->shift()));
                    break;
                }

            }
            
            array_push($_path, 'Index');
            break;

        }

        return 'Page\\' . implode('\\', $_path);
    }

    /**
     * Initialise page style
     *
     * @return void
     */
    protected function iniStyle( string $notice = '', bool $error = false)
    {
        $this->style->url = $this->url;
        $this->style->data = $this->data;
        $this->style->build = $this->build;
        $this->style->user = $this->user;
        $this->style->template = $this->template;
        $this->style->system = $this->system;
        $this->style->language = $this->language;
        $this->style->ID = $this->url->getAllID();

        $this->style->ini();

        if ($notice) {
            $this->style->notice($notice);
        }

        if ($error) {
            $this->style->error();
        } else {
            $this->style->show();
        }
    }

    /**
     * Shows error page
     *
     * @return void
     */
    protected function error()
    {
        $this->language             = new Language(
            language: $this->system->get('site.language')
        );

        $this->template             = new Template(
            template: $this->system->get('site.template'),
            path: '/Styles'
        );

        $this->iniStyle(
            error: true
        );
    }

    /**
     * Redirects user to predefined redirect user if is set otherwise redirects user to current url without operator
     *
     * @return void
     */
    protected function redirect()
    {
        if (self::$definedURL) {
            redirect(self::$definedURL);
        }

        redirect($this->url->getURL());
    }

    /**
     * Returns content of file
     * 
     * @param  string Path to file
     * @param  array $options Options
     *
     * @return string
     */
    protected function file( string $path, array $options = [] )
    {
        ob_start();

        extract($this->language->get());

        if ($options) {
            eval($options['variable'] . ' = $options[\'data\'];');
        }

        require($this->template->template($path));

        return ob_get_clean();
    }
    
    /**
     * Shows page with notice message
     *
     * @param  string $notice Language notice name
     * @param  array $assign Data to assign
     * 
     * @return void
     */
    public function notice( string $notice, array $assign = [] )
    {
        $lang = $this->language->get('L_NOTICE');

        $message = $lang['L_FAILURE'][$notice] ?? $lang['L_FAILURE_MESSAGE'];

        foreach ($assign as $variable => $data) {
            $message = strtr($message, ['{' . $variable . '}' => $data]);
        }
        
        if (defined('AJAX')) {
            echo json_encode([
                'status' => 'error',
                'error' => $message
            ]);
            exit();
        }

        $this->data->navbar = ($this->navbar ?? $this->page->navbar)->getData();

        $this->iniStyle(
            notice: $message
        );
    }

    /**
     * Abstract process method for every page
     *
     * @return void
     */
    abstract protected function body();
}