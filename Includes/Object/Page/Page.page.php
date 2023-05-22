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

namespace App\Page;

/**
 * Page
 */
abstract class Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = false;

    /**
     * @var bool $photoSwipe If true - JS library PhotoSwipe will be loaded 
     */
    protected bool $photoSwipe = false;

    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 3;

    /**
     * @var bool $header If true - big header will be showed
     */
    protected bool $header = false;

    /**
     * @var string $template Page template
     */
    protected string $template = '';

    /**
     * @var bool $notification If true - notifications will be displayed
     */
    protected bool $notification = false;

    /**
     * @var string $permission Required permission
     */
    protected string $permission = '';
    
    /**
     * @var \App\Model\Url $url Url
     */
    protected \App\Model\Url $url;

    /**
     * @var \App\Model\Data $data Data
     */
    protected \App\Model\Data $data;

    /**
     * @var \App\Model\Database\Query $db Query compiler
     */
    protected \App\Model\Database\Query $db;
    
    /**
     * @var \stdClass $build Build
     */
    protected \stdClass $build;

    /**
     * @var \App\Visualization\Navbar\Navbar $navbar Navbar
     */
    protected \App\Visualization\Navbar\Navbar $navbar;

    /**
     * @var array $IDs IDs positions and values
     */
    private static array $IDs = [];

    /**
     * Constructor
     */
    public function __construct( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Url $url, \stdClass $build = null )
    {
        // Database
        $this->db = $db;
        
        // URL
        $this->url = $url;
        
        // Data
        $this->data = $data;

        if (!is_null($build))
        {
            $this->build = $build;
        }

        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Language
        $language = $data->get('inst.language');

        // IF page has set any template
        if (isset($this->template) and $this->template)
        {
            // Add it to list of templates
            \App\Style\Style::setTemplate($this->template);
        }

        if (!in_array(get_class($this), ['App\Page\Router', 'App\Page\Admin\Router']))
        {
            // Remove permanently first parameter in URL
            //$this->url->shift();
        }
        
        // Set page description
        $data->set('data.head.description', $system->get('site.description'));

        // If page has set any required permission
        if ($this->permission)
        {
            // If user does not have required permission
            if ($permission->has($this->permission) === false)
            {
                // Show error page
                $this->error404();
            }
        }

        switch ($this->logged)
        {
            // If is required user to be loged in
            case 1:
                if ($user->isLogged() === false)
                {
                    // Show error page
                    $this->error404();
                }
            break;

            // If is required user to be logged out
            case 2:
                if ($user->isLogged() === true)
                {
                    // Show error page
                    $this->error404();
                }
            break;
        }
        
        // Load HTML editor
        if ($this->editor)
        {
            $data->set('options.editor', true);

            if ($system->get('site.mode.blog.profiles'))
            {
                // List of all registered users
                $users = $db->select('app.user.all()');

                $mentionUserList = [];

                foreach ($users as $_)
                {
                    $mentionUserList[$_['user_name']] = [
                        'link' => $this->build->url->profile($_),
                        'class' => 'username user--' . $_['group_class']
                    ];
                }

                // Save usernames of all users for mention in HTML editor
                $data->set('data.mentionUserList', json_encode($mentionUserList));
            }
        }
        
        // Load PhotoSwipe
        if ($this->photoSwipe)
        {
            $data->set('options.photoSwipe', true);
        }

        // Show big header
        if ($this->header)
        {
            $data->set('options.header', true);
        }

        // If page has set to display notifications
        if ($this->notification)
        {
            // Notification
            $notification = new \App\Visualization\Visualization([]);

            // Foreach each notifications
            foreach ($db->select('app.notification.all()') as $_)
            {
                // Notification is set as hidden
                if ($_['notification_hidden'])
                {
                    continue;
                }

                $notification
                    // Create new object(notification) and jump inside
                    ->create()->jumpTo()
                    // Set notification type
                    ->set('data.type', $_['notification_type'])
                    // Set notification name
                    ->set('data.title', $_['notification_name'])
                    // Set notification text
                    ->set('data.text', $_['notification_text'])
                    // Back to root object
                    ->root();
            }

            // Finish notification and get ready for generate
            $data->notification = $notification->getDataToGenerate();
        }

        // Get current page class
        // This array will be in the end compiled to final URL
        $pageClass = array_values(array_filter(explode('\\' , get_class($this))));

        if ($pageClass[0] == 'App')
        {
            // Remove "Page" class
            unset($pageClass[0]);

            // Re-index array
            $pageClass = array_values($pageClass);
        }


        if ($pageClass[0] != 'Plugin' || (isset($pageClass[2]) and $pageClass[2] == 'Plugin'))
        {
            // Set page name
            $data->set('data.page', get_class($this));

            // Set page name for HTML
            $data->set('data.page-html', strtolower(implode('-', array_slice($pageClass, 1))));
        }

        // Set page title according to class
        $data->set('data.head.title', $language->get('L_TITLE.\\' . implode('\\', array_slice($pageClass, 1))) ?: $data->get('data.head.title'));

        if (isset($pageClass[0]) and isset($pageClass[2]))
        {
            // If is loading additional plugin page on already exisitng page
            if ($pageClass[0] == 'Plugin' and $pageClass[3] != 'Plugin')
            {
                return;
            }

            // If user is visiting page from plugin
            if ($pageClass[0] == 'Plugin' and $pageClass[3] == 'Plugin')
            {
                // Plugin
                $plugin = $data->get('inst.plugin');

                // Find plugin by name
                $p = $plugin->findByName($pageClass[1]);

                // If plugin is not installed
                if (!$p->isInstalled())
                {
                    // Show error page
                    $this->error404();
                }
                
                // Set current loading plugin page class to plugin
                $p->setCurrentPage(get_class($this));
            }
        }

        // If is not inicialising router page
        if (!in_array(get_class($this), ['App\Page\Router', 'App\Page\Admin\Router']))
        {
            // If first class is "Plugin"
            if ($pageClass[0] == 'Plugin')
            {
                // If second parameter is set plugin name
                // Means that we are loading some plugin page
                while (isset($pageClass[1]))
                {
                    // And if another class is "Admin"
                    // Means that we are entering plugin's page for admin control page
                    if ($pageClass[3] == 'Admin')
                    {
                        // Change array values to build correct URL
                        $pageClass[0] = 'Admin';
                        $pageClass[1] = 'Plugin';
                        $pageClass[2] = 'Setup';

                        break;
                    }

                    // If code get there
                    // Means that now is loading some plugin's page which doesn't belong to admin panel
                    // Just normal plugin page accesible for users
                    
                    // Remove name of plugin
                    unset($pageClass[1]);
                    
                    // Remove "Page"
                    unset($pageClass[2]);

                    // Remove "Plugin"
                    unset($pageClass[3]);

                    break;
                }
            }

            if ($pageClass[0] == 'Page')
            {
                // Remove "Page" class
                unset($pageClass[0]);
            }

            // Re-index keys in array
            $pageClass = array_values($pageClass);

            // Search in array from "Tab" class
            $key = array_search('Tab', $pageClass);

            // If array really contains this class
            if ($key != false)
            {
                // Concatenate "tab" with immediately next lement in array
                // To correct URL generation in the end
                $pageClass[$key] = 'tab-' . mb_strtolower($pageClass[$key+1]);

                // Remove the let's say the name of tab
                unset($pageClass[$key+1]);
            }
            
            // If last loaded class has name "Index"
            if ($pageClass[array_key_last($pageClass)] === 'Index')
            {
                // Remove this element from array
                // Because we don't want to have in the end of the url the "/index/"
                unset($pageClass[array_key_last($pageClass)]);
            }

            // Re-index array
            $pageClass = array_values($pageClass);

            // If page require id
            if (isset($this->ID))
            {
                // Save to static variable position to key inserting and the key
                // Key = position to insert the key
                // Value = The key
                self::$IDs[count($pageClass) + count(self::$IDs)] = $this->url->getID(count(self::$IDs), false);
            }

            // Foreach every key
            foreach (self::$IDs as $position => $ID)
            {
                $i = 1;
                // Foreach every class
                foreach ($pageClass as $class)
                {
                    // If number of foreached class is same as position to insert a key
                    if ($i == $position)
                    {
                        // Place a key after $i-st element of array 
                        array_splice($pageClass, $i, 0, [$ID]);
                        break;
                    }
                    $i++;
                }
            }

            // Build new URL
            $this->url->set(strtolower(implode('/' , $pageClass)));

            // File model
            $file = new \App\Model\File\File();

            // Foreach every installed plugin
            foreach (LIST_OF_INSTALLED_PLUGINS as $item)
            {
                // If exists any page in plugin which want to change content on every page = \Page\Page
                // If this page exists in foreached plugin
                if ($file->exists('/Plugins/' . $item . '/Object/Page/Page.page.php'))
                {
                    // Build plugin page
                    $page = 'Plugin\\' . $item . '\Page\Page';
                    
                    // Initialise plugin page
                    $page = new $page(
                        db: $this->db,
                        url: $this->url,
                        data: $this->data,
                        build: $this->build
                    );

                    // Load plugin page
                    $page->body( $this->data, $this->db );
                }
            }
        }
    }

    /**
     * Runs process manually
     * 
     * @param string $method Method name
     * 
     * @return void
     */
    protected function runProcess( string $method )
    {
        $data = $this->data;

        $return = $this->{$method}( $data, $this->db, new \App\Model\Post );

        if ($return !== false)
        {
            if ($data->get('data.message.success'))
            {
                // Language
                $language = $data->get('inst.language');

                // Translate success message if exists
                if ($language = $language->get('L_NOTICE.L_SUCCESS.' . $data->get('data.message.success')))
                {
                    // Save to session
                    \App\Model\Session::put('success', $language);
                }
            }

            if ($data->get('data.redirect'))
            {
                redirect($this->url->build($data->get('data.redirect')));
            }
        }

        redirect($this->url->build($this->url->getURL()));
    }

    /**
     * Checks if ajax was send
     * 
     * @return void
     */
    public function checkForAjax()
    {
        // Check if user is approaching to ajax
        if ($this->url->get('action') == 'ajax')
        {
            // Language
            $language = $this->data->get('inst.language');

            // Ajax name
            $ajax = $_POST['ajax'];

            if (!defined('AJAX'))
            {
                define('AJAX', true);
            }

            // Check if name of ajax was send
            if (!isset($ajax) or empty($ajax))
            {
                return;
            }

            // If ajax method with settings does not exist
            if (!method_exists($this, 'ajax'))
            {
                return;
            }

            // Get name of method to execute
            $method = $this->ajax($ajax);
            if (!$method)
            {
                return;
            }

            while (method_exists($this, 'ajaxPermisssion'))
            {
                // User
                $user = $this->data->get('inst.user');

                $require = $this->ajaxPermission($ajax);
                if (!$require)
                {
                    break;
                }

                if ($require == true)
                {
                    if (!$user->isLogged())
                    {
                        echo json_encode(['status' => 'error']);
                        exit();
                    }

                    break;
                }

                // Permission
                $permission = $user->get('permission');

                if (!$permission->has($ajax[2]))
                {
                    echo json_encode(['status' => 'error']);
                    exit();
                }

                break;
            }

            // If any another data are required
            if (method_exists($this, 'ajaxData'))
            {
                $data = $this->ajaxData($ajax);

                foreach ($data as $name => $type)
                {
                    $value = $_POST[$name] ?? '';

                    if (gettype($value) != $type)
                    {
                        
                        $value = match($type)
                        {
                            ARR => [],
                            STRING => ''
                        };
                    }

                    if (is_string($value))
                    {
                        // Remove HTML Characters
                        $value = strip_tags($value);

                        // Translate &nbsp;(non-breaking space) to normal space
                        $value = str_replace('&nbsp;', ' ', $value);

                        // Remove whitespaces from the beggining and end
                        $value = trim($value);
                    }

                    if (!isset($_POST[$name]) or empty($value))
                    {
                        $JSON = [
                            'status' => 'error'
                        ];

                        if ($language = $language->get('L_NOTICE.L_FAILURE.' . $name))
                        {
                            $JSON['message'] = $language;
                        }

                        echo json_encode($JSON);

                        exit();
                    }
                }
            }

            // Run ajax
            $return = $this->{$method}( $this->data, $this->db, new \App\Model\Post );

            $JSON = [
                'status' => 'error'
            ];

            if ($return === false)
            {
                if ($language = $language->get('L_NOTICE.L_FAILURE.' . $method))
                {
                    $JSON['message'] = $language;
                }

                echo json_encode($JSON);

                exit();
            }

            $JSON['status'] = 'ok';

            if ($return)
            {
                $JSON['data'] = $return;
            }

            if ($this->data->get('options.refresh'))
            {
                $JSON['refresh'] = true;
            }

            if ($this->data->get('data.redirect'))
            {
                $JSON['redirect'] = $this->data->get('data.redirect');
            }

            if ($language = $language->get('L_NOTICE.L_SUCCESS.' . $method))
            {
                if ($this->data->get('data.redirect') or $this->data->get('options.refresh'))
                {
                    // Save to session
                    \App\Model\Session::put('success', $language);
                } else $JSON['message'] = $language;
            }

            echo json_encode($JSON);

            exit();
        }
    }
    
    /**
     * Builds page
     *
     * @return object
     */
    protected function buildPage( string $object = 'App\Page', string $suffix = 'page', string $path = null, string $class = '' )
    {
        if ($class)
        {
            return new $class(
                db: $this->db,
                url: $this->url,
                data: $this->data,
                build: $this->build
            );
        }
        
        $_path = [];

        // Default path
        if (!$path)
        {
            $path = ROOT . '/Includes/Object/Page';

            $namespace = explode('\\', get_class($this));
            $namespace = array_slice($namespace, 2, count($namespace) - 3);

            if (empty($namespace) === false)
            {
                $path .= '/' . implode('/', $_path = $namespace);
            }
        } else {
            $path = ROOT . $path;
        }

        while (true)
        {
            if (!empty($this->url->get()))
            {
                // If dir exists
                if (is_dir($path . '/' . ucfirst($this->url->get(1)) . '/'))
                {
                    array_push($_path, $shift = ucfirst($this->url->shift()));
                    $path .= '/' . $shift;

                    if (file_exists($path . '/Router.' . $suffix . '.php'))
                    {
                        array_push($_path, 'Router');
                        break;
                    }
                    
                    continue;
                }
                
                // If page exists
                if (file_exists($path . '/' . ucfirst($this->url->get(1)) . '.' . $suffix . '.php'))
                {
                    array_push($_path, ucfirst($this->url->shift()));
                    break;
                }
            }

            // If index page exists
            if (file_exists($path . '/Index.' . $suffix . '.php'))
            {
                array_push($_path, 'Index');
                break;
            }
            
            return;
        }

        $page = $object . '\\' . implode('\\', $_path);
        return new $page(
            db: $this->db,
            url: $this->url,
            data: $this->data,
            build: $this->build
        );
    }

    /**
     * Ends page and display page
     * 
     * @param string $notice
     *
     * @return void
     */
    protected function loadPageFromPlugins()
    {
        // File model
        $file = new \App\Model\File\File();
            
        // Foreach every installed plugin
        foreach (LIST_OF_INSTALLED_PLUGINS as $item)
        {
            // If in plugin exists same page
            if ($file->exists('/Plugins/' . $item . '/Object/' . str_replace('\\', '/', str_replace('App\\', '', get_class($this)) . '.page.php')))
            {
                // Build class of plugin page
                $page = 'Plugin\\' . $item . '\\' . str_replace('App\\', '', get_class($this));
                // Initialise plugin page
                $page = new $page(
                    db: $this->db,
                    url: $this->url,
                    data: $this->data,
                    build: $this->build
                );

                // Load plugin page
                $page->body( $this->data, $this->db );
            }
        }
    }

    /**
     * Ends page and display page
     * 
     * @param string $notice
     *
     * @return void
     */
    public function end( string $notice = '', array $assign = [] )
    {
        // Data
        $data = $this->data;
        
        // If page will be loaded with any notice
        if ($notice)
        {
            // Language
            $language = $data->get('inst.language');

            // If notice from application
            // And has format to be translated
            if (count(explode(' ', $notice)) == 1)
            {
                // Get translated notice from language
                $notice = $language->get('L_NOTICE.L_FAILURE.' . $notice);
                // If notice doesn't exist
                if (!$notice)
                {
                    // Set default notice
                    $notice = $language->get('L_NOTICE.L_FAILURE_MESSAGE');
                }
            }

            // Insert to notice values
            foreach ($assign as $variable => $_data)
            {
                $notice = strtr($notice, ['{' . $variable . '}' => $_data]);
            }

            // If ajax is enabled
            if (defined('AJAX'))
            {
                // Display error in JSON 
                echo json_encode([
                    'status' => 'error',
                    'message' => $notice
                ]);
                
                exit();
            }

            // Save notice to data
            $data->set('data.message', [
                'text' => $notice,
                'type' => 'warning'
            ]);
        }

        // If ajax is enabled
        if (defined('AJAX'))
        {
            exit();
        }

        // If is set success message in session
        if (\App\Model\Session::exists('success'))
        {
            // Add it to data
            $data->set('data.message', [
                'text' => \App\Model\Session::get('success'),
                'type' => 'success'
            ]);

            // Remove message from session
            \App\Model\Session::delete('success');
        }

        // Load style object
        // This object generate template
        $style = new \App\Style\Style(
            // Give it loaded data
            data: $this->data,
            url: $this->url,
            build: $this->build,
        );

        // If navbar wasn't saved yet
        // This happen only in admin panel pages
        if (!$data->navbar->get('body'))
        {
            // Save it
            $data->navbar = $this->navbar->getDataToGenerate();
        }

        // Generate page
        $style->show();
    }

    /**
     * Shows error page
     *
     * @return void
     */
    protected function error404()
    {
        // Data
        $data = $this->data;

        // System
        $system = $data->get('inst.system');

        // Language
        $language = $data->get('inst.language');

        // Set url to error page
        $this->url->set('/error/');

        // Set default template
        // Because if any admin page was loaded we must load the default tempalte
        // for website not for admin panel
        $template = new \App\Model\Template(
            path: '/Styles',
            template: $system->get('site.template')
        );

        // If in session is saved template to preview
        if ($data->get('data.preview'))
        {
            // Set preview template
            $template = new \App\Model\Template(
                path: '/Styles',
                template: $data->get('data.preview')
            );
        }

        // Set default language
        // Because if any admin page was loaded we must load the basic language
        // for website not for admin panel
        $language = $data->get('inst.language');
        $language->load( language: $system->get('site.language'), template: $template, folder: 'website' );

        // If ajax is enabled
        if (defined('AJAX'))
        {
            // Display error in JSON
            echo json_encode([
                'status' => 'error',
                'message' => $language->get('L_ERROR_DESC')
            ]);
            
            exit();
        }

        // Set page favicon
        $favicon = '/Uploads/Site/PHPCore_icon.svg';
        if ($system->get('site.favicon'))
        {
            $favicon = '/Uploads/Site/Favicon.' . $system->get('site.favicon');
        }
        $data->set('data.head.favicon', $favicon);

        // Set page name
        $data->set('data.page', 'App\Page\Error');

        // Set page title
        $data->set('data.headtitle', $language->get('L_TITLE.\Error'));

        // Load style object
        // This object generate template
        $style = new \App\Style\Style(
            // Give it data
            data: $this->data,
            url: $this->url,
            build: $this->build,
            // Display 404 error page
            e404: true
        );

        // Generate page
        $style->show();
    }

    /**
     * Redirects user
     *
     * @param  string $path URL
     * 
     * @return void
     */
    protected function redirect( string $path )
    {
        redirect(\App\Model\Url::build($path));
    }

    /**
     * Checks if form was submitted
     * 
     * @return void
     */
    protected function checkFormSubmit()
    {
        // Data
        $data = $this->data;

        // File
        $file = new \App\Model\File\File();

        if (!isset($data->form))
        {
            return;
        }

        foreach ($data->form->get('body') as $form)
        {
            // Make visualizator from data
            // for better manipulating
            $form = new \App\Visualization\Visualization($form);

            // If form does not have set submit button
            if (!$form->get('data.button.submit.name'))
            {
                continue;
            }

            // If submit button was not pressed
            if (!isset($_POST[$form->get('data.button.submit.name')]))
            {
                continue;
            }

            // Form does not have set success method
            if (!$form->get('options.success.method'))
            {
                continue;
            }

            // Success method does not match with POST method
            if ($form->get('options.success.method') != $_POST['method'])
            {
                continue;
            }

            foreach ($form->get('body') as $frame)
            {
                $frame = new \App\Visualization\Visualization($frame);
                
                foreach ($frame->get('body') as $name => $input)
                {
                    $input = new \App\Visualization\Visualization($input);

                    // System
                    $system = $data->get('inst.system');

                    // Permission
                    $permission = $data->get('inst.user')->get('permission');

                    $name = str_replace('.', '_', $name);

                    if (in_array($input->get('options.type'), ['select', 'select[]', 'radio', 'checkbox[]']))
                    {
                        $filter = [];
                        foreach ($input->get('body') ?: [] as $option)
                        {
                            $option = new \App\Visualization\Visualization($option);

                            array_push($filter, $option->get('data.value'));
                        }

                        if (!isset($_POST[$name]))
                        {
                            $_POST[$name] = '';

                            if (str_ends_with($input->get('options.type'), '[]'))
                            {
                                $_POST[$name] = [];
                            }
                        }

                        $arr = $_POST[$name];
                        if (!is_array($arr))
                        {
                            $arr = [$arr];
                        }

                        if (!array_intersect($filter, $arr))
                        {
                            $_POST[$name] = '';

                            if (str_ends_with($input->get('options.type'), '[]'))
                            {
                                $_POST[$name] = [];
                            }
                        }
                        
                    }

                    // Input is required
                    if ($input->get('options.required'))
                    {
                        // Input is not hidden
                        if (!$input->get('options.hide'))
                        {
                            if (str_starts_with($input->get('options.type'), 'file/') and !isset($_FILES[$name]))
                            {
                                throw new \App\Exception\Notice($name);
                            }
                            
                            if (!str_starts_with($input->get('options.type'), 'file/') and (!isset($_POST[$name]) or trim($_POST[$name]) == ''))
                            {
                                throw new \App\Exception\Notice($name);
                            }
                        }
                    }

                    if (!isset($_POST[$name])) 
                    {
                        switch ($input->get('options.type'))
                        {
                            case 'text':
                            case 'html':
                                $_POST[$name] = '';
                            break;

                            case 'number':
                            case 'checkbox':
                                $_POST[$name] = 0;
                            break;

                            case 'file/image':
                            case 'file/image[]':
                            case 'file/misc':
                            case 'file/misc[]':
                            case 'file/zip':
                            case 'file/zip[]':
                                $_POST[$name] = $file->form($name, $input->get('options.type'));
                            break;
                        }
                    }

                    switch ($input->get('options.type'))
                    {
                        case 'text':
                        case 'html':

                            if (!is_string($_POST[$name]))
                            {
                                $_POST[$name] = '';
                                continue;
                            }

                            $_POST[$name] = trim($_POST[$name]);

                        break;

                        case 'checkbox':
                            if ($_POST[$name] != 1)
                            {
                                $_POST[$name] = 0;
                                continue;
                            }
                        break;
    
                        case 'number':

                            if (!ctype_digit($_POST[$name]))
                            {
                                $_POST[$name] = 0;
                                continue;
                            }

                        break;

                        case 'file/image':
                        case 'file/image[]':
                        case 'file/misc':
                        case 'file/misc[]':
                        case 'file/zip':
                        case 'file/zip[]':

                            $_POST[$name] = $image = $file->form($name, $input->get('options.type'));

                            if (!is_array($image))
                            {
                                // If file is image
                                if (in_array($input->get('options.type'), ['file/image', 'file/image[]']))
                                {
                                    // If system has allowed to uplaod gif images
                                    if ($system->get('image.gif') == 1)
                                    {
                                        // If logged user has permission to uplaod gif images
                                        if ($permission->has('image.gif'))
                                        {
                                            // Allow uplaod GIF image
                                            $image->allowGIF();
                                        }
                                    }
                                }
                            }

                            if (str_ends_with($input->get('options.type'), '[]'))
                            {
                                $image = array_slice($image, 0, 20);
                                foreach ($image as $_)
                                {
                                    // If file is image
                                    if (in_array($input->get('options.type'), ['file/image', 'file/image[]']))
                                    {
                                        // If system has allowed to uplaod gif images
                                        if ($system->get('image.gif') == 1)
                                        {
                                            // If logged user has permission to uplaod gif images
                                            if ($permission->has('image.gif'))
                                            {
                                                // Allow uplaod GIF image
                                                $_->allowGIF();
                                            }
                                        }
                                    }

                                    $_->check();
                                }
                                $_POST[$name] = $image;
                                break;
                            }
                            $image->check();
                            $_POST[$name] = $image;
                        break;
                    }

                    if ($input->get('options.max-length'))
                    {
                        $count = match(gettype($_POST[$name]))
                        {
                            'string' => mb_strlen($_POST[$name]),
                            'array' => count($_POST[$name]),
                            'integer' => $_POST[$name]
                        };

                        if ($count > $input->get('options.max-length'))
                        {
                            throw new \App\Exception\Notice($name . '_length_max');
                        }
                    }

                    switch ($input->get('options.type'))
                    {
                        case 'text':
                        case 'email':
                        case 'username':
                        case 'password':
                            $_POST[$name] = strip_tags($_POST[$name]);
                        break;

                        case 'html':
                            $HTML = new \App\Model\HTMLPurifier($input->get('options.html') ?: 'big');
                            $_POST[$name] = $HTML->purify($_POST[$name]);
                        break;
                    }
                }
            }

            $return = $form->get('options.success.page')->{$form->get('options.success.method')}( $data, $this->db, new \App\Model\Post );

            if ($return !== false)
            {
                if ($data->get('data.message.success'))
                {
                    // Language
                    $language = $data->get('inst.language');

                    // Translate success message if exists
                    if ($language = $language->get('L_NOTICE.L_SUCCESS.' . $data->get('data.message.success')))
                    {
                        // Save to session
                        \App\Model\Session::put('success', $language);
                    }
                }

                if ($data->get('data.redirect'))
                {
                    redirect($this->url->build($data->get('data.redirect')));
                }
            }

            redirect($this->url->build($this->url->getURL()));
        }
    }
}