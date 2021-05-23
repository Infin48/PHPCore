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

/**
 * Page
 */
abstract class Page
{
    /**
     * @var array $listOfOperations List of operations
     */
    private array $listOfOperations = ['add', 'move', 'deleteall', 'new', 'edit', 'delete', 'up', 'down', 'activate', 'lock', 'unlock', 'stick', 'unstick', 'send', 'leave', 'mark', 'back', 'like', 'unlike', 'set', 'refresh'];

    /**
     * @var string $definedURL Stored page or folder pre-defined redirect url
     */
    private static string $definedURL = '';

    /**
     * @var string $templateName Name of default template
     */
    protected string $templateName = '';
    
    /**
     * @var string $favicon Favicon
     */
    protected string $favicon = '/Uploads/Site/PHPCore_icon.svg';
    
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
     * @var \Model\System\System $system System
     */
    protected \Model\System\System $system;

    /**
     * @var \Visualization\Navbar\Navbar $navbar Navbar
     */
    protected \Visualization\Navbar\Navbar $navbar;

    /**
     * @var array $parsedURL Parsed URL
     */
    protected static array $parsedURL = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        if (self::$parsedURL){
            self::$parsedURL = array_values(array_filter(self::$parsedURL));
        }
    }
    
    /**
     * Initialise page
     *
     * @return void
     */
    protected function initialise()
    {
        $pageClass = $org = array_values(array_filter(explode('\\', get_class($this))));
        
        foreach (['Page', 'Index', 'Router'] as $item) {

            if (in_array($item, $pageClass)) {
                unset($pageClass[array_search($item, $pageClass)]);
            }
        }
        
        if (in_array(strtolower($pageClass[array_key_last($pageClass)] ?? ''), $this->listOfOperations)) {
            array_pop($pageClass);
        }

        $this->style->URL = $this->system->url->build(mb_strtolower(implode('/', array_filter($pageClass))));
        
        if (isset($this->settings['id'])) {

            self::$parsedURL[0] ?? [] or $this->error();

            $this->style->ID = explode('.', self::$parsedURL[0])[0];
            
            if ($this->settings['id'] == int) {
                if (!ctype_digit($this->style->ID)) {
                    $this->error();
                }
            }

            $this->style->URL .= self::$parsedURL[0] . '/';
        }

        if (!in_array('Router', $org)) {
            define('URL', $this->style->URL);
        }
        
        $this->data->head['title'] = $this->language->get('L_TITLE')[get_class($this)] ?? $this->data->head['title'];
        
        $this->process->url($this->getURL());

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
                        $this->templateName = $this->settings['template'];
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
    protected function build()
    {
        $_path = [];

        // DEFAULT PATH
        $path = ROOT . '/Includes/Object/Page/';

        $namespace = explode('\\', get_class($this));
        $namespace = array_slice($namespace, 1, count($namespace) - 2);
        if (empty($namespace) === false) {
            $path .= implode('/', $_path = $namespace) . '/';
        }

        while (true) {
            if (!empty(self::$parsedURL)) {

                // IF DIR EXISTS
                if (is_dir($path . ucfirst(self::$parsedURL[0]) . '/')) {

                    array_push($_path, $shift = ucfirst(array_shift(self::$parsedURL)));
                    $path .= $shift . '/';

                    if (file_exists($path . '/Router.page.php')) {
                        array_push($_path, 'Router');
                    break;
                    }

                    continue;
                }

                // IF PAGE EXISTS
                if (file_exists($path . ucfirst(self::$parsedURL[0]) . '.page.php')) {

                    $shifted = array_shift(self::$parsedURL);
                    array_push($_path, ucfirst($shifted));
                    break;
                }

            }
            
            if ($this->url->is('edit') or $this->url->is('add')) {
                
                $param = $this->url->is('edit') ? 'Edit' : 'Add';
                if (file_exists($path . $param . '.page.php')) {
                    array_push($_path, $param);

                    break;
                }
            }
            
            if (empty($shifted)) {
                array_push($_path, 'Index');
            }
            break;

        }

        return 'Page\\' . implode('\\', $_path);
    }
    
    /**
     * Returns url without names of operations
     *
     * @return string
     */
    protected function getURL()
    {
        return $this->style->URL;
    }

    /**
     * Returns page item ID
     *
     * @return string|int
     */
    protected function getID()
    {
        return $this->style->ID;
    }

    /**
     * Shows error page
     *
     * @return void
     */
    protected function error()
    {
        $this->style->load($this->data, $this->build, $this->user);
        $this->style->error();
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

        redirect($this->getURL());
    }

    /**
     * Returns content of file
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
        $message = $this->language->get('L_NOTICE')['L_FAILURE'][$notice] ?? $notice;

        if ($message) {
            foreach ($assign as $variable => $data) {
                $message = strtr($message, ['{' . $variable . '}' => $data]);
            }
        }
        
        if (AJAX === true) {
            echo json_encode([
                'status' => 'error',
                'error' => $message
            ]);
            exit();
        }

        $this->data->navbar = ($this->navbar ?? $this->page->navbar)->getData();
        $this->style->load($this->data, $this->build, $this->user);
        if ($message) {
            $this->style->notice($message);
        }
        $this->style->show();
    }

    /**
     * Abstract process method for every page
     *
     * @return void
     */
    abstract protected function body();
}