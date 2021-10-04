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
use Model\System;
use Model\Session;
use Model\Template;
use Model\Language;
use Model\Build\Build;
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
     * Body of this page
     *
     * @return void
     */
    public function body()
    {
        $this->style                = new Style();
        $this->style->setTemplate('/Body');
        
        $this->data                 = new Data();

        $this->loadPlugins();

        $this->system               = new System();
        
        $this->user                 = new User();

        $this->url                  = new Url();

        $this->process              = new Process();
        $this->process->system      = $this->system;
        $this->process->perm        = $this->user->perm;

        // DEFAULT TEMPLATE
        $this->template             = new Template(
            template: $this->system->get('site.template'),
            path: '/Styles'
        );

        // DEFAULT LANGUAGE
        $this->language             = new Language(
            language: $this->system->get('site.language'),
            plugins: $this->data->data['plugins']
        );

        // BUILDERS
        $this->build                = (new Build())->load();
        $this->build->system        = $this->system;
        $this->build->language      = $this->language;



        if (Session::exists('preview')) {

            if (file_exists(ROOT . '/Styles/' . Session::get('preview'))) {

                // SET PREVIEW TEMPLATE
                $this->template   = new Template(
                    template: Session::get('preview'),
                    path: '/Styles'
                );

                $this->data->data['preview'] = Session::get('preview');
            }
        }

        // SET PAGE FAVICON
        $favicon = '/Uploads/Site/PHPCore_icon.svg';
        if ($this->system->get('site.favicon')) {
            $favicon = '/Uploads/Site/Favicon.' . $this->system->get('site.favicon');
        }
        $this->data->head['favicon'] =  $favicon;
        
        // DEFAULT PAGE TITLE
        $this->data->head['title']          = $this->system->get('site.name');

        // DEFAULT PAGE DESCRIPTION
        $this->data->head['description']    = $this->system->get('site.description');

        setlocale(LC_ALL, $this->system->get('site.locale').'.UTF-8');
        date_default_timezone_set($this->system->get('site.timezone'));

        
        $button = new Button();
        $buttonSub = new ButtonSub();
        
        // NAVBAR
        $this->navbar = new Navbar('/Basic');
        $this->navbar->object('menu')->fill(data: $button->getAll(), function: function ( \Visualization\Navbar\Navbar $navbar ) use ($buttonSub) { 
            
            // IF IS LOCAL BUTTON
            if ($navbar->obj->get->data('button_link_type') == 1) {
                
                // BUILD LINK
                $navbar->obj->set->data('button_link', $this->url->build($navbar->obj->get->data('button_link')));
            }
            
            if ($navbar->obj->get->data('button_dropdown') == 1) {
                
                $navbar->fill(data: $buttonSub->getParent($navbar->obj->get->data('button_id')), function: function ( \Visualization\Navbar\Navbar $navbar ) { 
                    
                    // IF IS LOCAL BUTTON
                    if ($navbar->obj->get->data('button_sub_link_type') == 1) {
                        
                        // BUILD LINK
                        $navbar->obj->set->data('button_sub_link', $this->url->build($navbar->obj->get->data('button_sub_link')));
                    }
                });
            }
        });
        
        if ($this->user->isLogged()) {
            
            // USER CONSTANTS
            define('LOGGED_USER_ID', $this->user->get('user_id'));
            define('LOGGED_USER_GROUP_INDEX', $this->user->get('group_index'));
            define('LOGGED_USER_GROUP_ID', $this->user->get('group_id'));
            
            if ($this->url->is('mark')) {
                $query = new Query();
                $query->query('
                DELETE un FROM ' . TABLE_USERS_NOTIFICATIONS . '
                WHERE user_notification_id = ? AND to_user_id = ?
                ', [$this->url->get('mark'), LOGGED_USER_ID]);
            }
            
            $userNotification = new UserNotification();
            
            $this->navbar->object('logged')->show()
            ->row('user')->option('profile')->setData('href', '$' . $this->build->url->profile($this->user->get()))
            ->row('notification')->fill(data: $notifi = $userNotification->getParent(LOGGED_USER_ID))
            ->row('notification')->notifiCount(count($notifi));
            
        } else {
            
            // USER CONSTANTS
            define('LOGGED_USER_ID', 0);
            define('LOGGED_USER_GROUP_ID', 0);
            define('LOGGED_USER_GROUP_INDEX', 0);
            
            $this->navbar->object('not-logged')->show();
            
            if ($this->system->get('registration.enabled') == 1) {
                $this->navbar->object('not-logged')->row('register')->show();
            } 
        }
        
        $controllerName = $this->build();   
        
        $ex = explode('\\', $controllerName);
        array_shift($ex);
        $this->data->data('pageName', strtolower($ex[0]) . ( isset($ex[1]) ? ((!is_null($ex[1]) and strtolower($ex[1]) != 'router') ? ' ' . strtolower(implode('-', $ex)) : '') : ''));
        
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
        
        $this->page->ini();
        $this->page->body();
        
        foreach ($this->data->data['plugins'] as $item) {
            if (file_exists(ROOT . '/Plugins/' . $item . '/Object/' . str_replace('\\', '/', $controllerName) . '.page.php')) {
                
                $page = 'Page\Plugin\\' . $item . '\\' . str_replace('Page\\', '', $controllerName);
                $this->page = new $page;
                $this->page->url = $this->url;
                $this->page->data = $this->data;
                $this->page->style = $this->style;
                $this->page->user = $this->user;
                $this->page->build = $this->build;
                $this->page->system = $this->system;
                $this->page->process = $this->process;
                $this->page->language = $this->language;
                $this->page->template = $this->template;

                $this->page->body();
            }
        }

        if ($this->user->isLogged()) {
            $this->navbar->object('logged')->row('conversation')->notifiCount(count($this->user->get('unread')));
        }
        $this->data->navbar = $this->navbar->getData();
        $this->iniStyle();
    }
}