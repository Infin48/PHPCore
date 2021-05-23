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

use Block\Button;
use Block\ButtonSub;
use Block\UserNotification;

use Model\Url;
use Model\User;
use Model\Data;
use Model\Template;
use Model\Language;
use Model\Build\Build;
use Model\System\System;
use Model\Database\Query;

use Process\Process;

use Style\Style;

use Visualization\Navbar\Navbar;

/**
 * Router
 */
class Router extends Page
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->system = new System();
        $this->language = new Language();
        $this->language->load('/Languages/' . $this->system->settings->get('site.language'));
        $this->data = new Data();
        $this->user = new User();
        $this->template = new Template();
        $this->build = new Build();
        $this->build->load();
        $this->process = new Process($this->system, $this->user->perm);
        $this->style = new Style($this->system);
        $this->parseURL();
        $this->url = new Url(self::$parsedURL);
    }
    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body()
    {
        $this->style->setTemplate('Body');

        setlocale(LC_ALL, $this->system->settings->get('site.locale').'.UTF-8');

        date_default_timezone_set($this->system->settings->get('site.timezone'));

        $this->data->data([
            'favicon' => $this->system->settings->get('site.favicon') ? '/Uploads/Site/Favicon.' . $this->system->settings->get('site.favicon') : '/Uploads/Site/PHPCore_icon.svg'
        ]);

        define('TEMPLATE_PATH_DEFAULT', '/Styles');

        define('TEMPLATE_DEFAULT', $this->system->settings->get('site.template'));

        $controllerName = $this->build();

        $this->data->head['title'] = $this->system->settings->get('site.description');
        $this->data->head['description'] = $this->system->settings->get('site.description');

        if ($this->user->isLogged()) {

            define('LOGGED_USER_ID', $this->user->get('user_id'));
            define('LOGGED_USER_GROUP_INDEX', $this->user->get('group_index'));
            define('LOGGED_USER_GROUP_PERMISSION', $this->user->get('groupPermission'));
            define('LOGGED_USER_GROUP_ID', $this->user->get('group_id'));

            if ($controllerName === 'Page\Admin\Router') {

                define('TEMPLATE_PATH', '/Includes/Admin/Styles');
                define('TEMPLATE', 'Default');

                $this->page = new $controllerName;

                $this->page->url = $this->url;
                $this->page->data = $this->data;
                $this->page->style = $this->style;
                $this->page->build = $this->build;
                $this->page->system = $this->system;
                $this->page->process = $this->process;
                $this->page->language = $this->language;
                $this->page->template = $this->template;

                $this->page->user = $this->user;
                $this->page->initialise();
                $this->page->body();

                $this->style->load($this->data, $this->build, $this->user);
                $this->style->show();
                exit();
            }
        }

        if (!defined('LOGGED_USER_GROUP_ID')) {
            define('LOGGED_USER_GROUP_ID', 0);
        }

        if (!defined('LOGGED_USER_GROUP_ID')) {
            define('LOGGED_USER_GROUP_ID', 0);
        }

        if (!defined('LOGGED_USER_ID')) {
            define('LOGGED_USER_ID', 0);
        }

        define('TEMPLATE_PATH', '/Styles');
        define('TEMPLATE', $this->system->settings->get('site.template'));

        $button = new Button();
        $buttonSub = new ButtonSub();

        $this->navbar = new Navbar('Basic');
        
        foreach ($button->getAll() as $dropdown) {

            $this->navbar->object('menu')->appTo($dropdown)->jumpTo();

            if ((bool)$dropdown['is_dropdown'] === true) {
        
                $this->navbar->fill($buttonSub->getParent($dropdown['button_id']));
            }
        }
        
        if ($this->user->isLogged()) {

            $userNotification = new UserNotification();

            if ($this->url->is('mark')) {
                $query = new Query();
                $query->query('
                    DELETE un FROM ' . TABLE_USERS_NOTIFICATIONS . '
                    WHERE user_notification_id = ? AND to_user_id = ?
                ', [$this->url->get('mark'), LOGGED_USER_ID]);
            }

            
            $this->navbar->object('logged')->show();
            $this->navbar->object('logged')->row('user')->option('profile')->setData('href', $this->build->url->profile($this->user->get()));

            $this->navbar->object('logged')->row('notification')->fill($notifi = $userNotification->getParent(LOGGED_USER_ID));
            $this->navbar->object('logged')->row('notification')->notifiCount(count($notifi));
            
        } else {

            $this->navbar->object('not-logged')->show();

            if ($this->system->settings->get('registration.enabled') == 1) {
                $this->navbar->object('not-logged')->row('register')->show();
            }
            
        }
        
        if (str_contains($controllerName, 'Page\Ajax')) {
            define('AJAX', true);
        } else define('AJAX', false);

        $ex = explode('\\', $controllerName);
        array_shift($ex);
        $this->data->data([
            'pageName' => strtolower($ex[0]) . ( isset($ex[1]) ? ((!is_null($ex[1]) and strtolower($ex[1]) != 'router') ? ' ' . strtolower(implode('-', $ex)) : '') : '')
        ]);
        
        $this->page = new $controllerName;
        $this->page->url = $this->url;
        $this->page->data = $this->data;
        $this->page->style = $this->style;
        $this->page->user = $this->user;
        $this->page->build = $this->build;
        $this->page->system = $this->system;
        $this->page->process = $this->process;
        $this->page->language = $this->language;
        $this->page->template = $this->template;

        $this->page->initialise();
        $this->page->body();

        if (AJAX) {
            echo json_encode($this->data->data);
            exit();
        }

        if ($this->user->isLogged()) {
            $this->navbar->object('logged')->row('conversation')->notifiCount(count($this->user->get('unread')));
        }

        $this->data->navbar = $this->navbar->getData();
        $this->style->load($this->data, $this->build, $this->user);
        $this->style->show();
        exit();
    }

    /**
     * Prases url
     *
     * @param string $url
     * 
     * @return array
     */
    protected function parseURL()
    {
        $i = 1;
        $url = urldecode($_SERVER['REQUEST_URI']);
        $parsedURL = explode('/', $url);
        unset($parsedURL[0]);

        foreach ($parsedURL as $parameter) {
            $_ex = explode('-', $parameter);
            if (count($_ex) > 1) {
                if ($_ex[0] === $this->system->url->getPage()) {
                    $page = trim(strip_tags($_ex[1]));
                    unset($parsedURL[$i]);
                }
            }

            $i++;
        }

        define('PAGE', $page ?? 1);
        self::$parsedURL = array_values(array_filter(explode('/', $this->system->url->translate(implode('/', $parsedURL)))));
    }

}