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

namespace Page\Admin;

use Block\Report;
use Block\Plugin;
use Block\Deleted;

use Model\Url;
use Model\User;
use Model\Data;
use Model\Template;
use Model\Language;
use Model\Build\Build;
use Model\System;

use Process\Process;

use Style\Style;

use Visualization\Navbar\Navbar;

/**
 * Router
 */
class Router extends \Page\Page
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

        $plugins = new Plugin();
        $this->language             = new Language(
            language: $this->system->get('site.language'),
            admin: true,
            plugins: array_column($plugins->getAll(), 'plugin_name_folder')
        );
        
        $this->build                = (new Build())->load();
        $this->build->system        = $this->system;
        $this->build->language      = $this->language;
        
        $this->user                 = new User();

        $this->url                  = new Url();
        
        $this->process              = new Process();
        $this->process->system      = $this->system;
        $this->process->perm        = $this->user->perm;

        $this->template             = new Template(
            template: 'Default',
            templateInitial: $this->system->get('site.template'),
            path: '/Includes/Admin/Styles'
        );

        // DEFAULT PAGE TITLE
        $this->data->head['title']          = $this->system->get('site.name');

        // DEFAULT PAGE DESCRIPTION
        $this->data->head['description']    = $this->system->get('site.description');

        // SET PAGE FAVICON
        $favicon = '/Uploads/Site/PHPCore_icon.svg';
        if ($this->system->get('site.favicon')) {
            $favicon = '/Uploads/Site/Favicon.' . $this->system->get('site.favicon');
        }
        $this->data->head['favicon'] =  $favicon;

        if ($this->user->isLogged() === false) {
            $this->error();
        }

        if ((bool)$this->user->perm->has('admin.?') == false) {
            $this->error();
        }

        setlocale(LC_ALL, $this->system->get('site.locale').'.UTF-8');
        date_default_timezone_set($this->system->get('site.timezone'));

        if ($this->user->isLogged()) {

            define('LOGGED_USER_ID', $this->user->get('user_id'));
            define('LOGGED_USER_GROUP_INDEX', $this->user->get('group_index'));
            define('LOGGED_USER_GROUP_ID', $this->user->get('group_id'));
        } else {
            define('LOGGED_USER_ID', 0);
            define('LOGGED_USER_GROUP_INDEX', 0);
            define('LOGGED_USER_GROUP_ID', 0);
        }

        $controllerName = $this->build();

        $this->page = new $controllerName;
        
        $this->navbar = new Navbar('/Admin');
        $this->navbar->perm = $this->user->perm;

        $report = new Report();
        $deleted = new Deleted();

        if (($count = $report->getCount())['total'] != 0) {
            $this->navbar->object('forum')->row('reported')->notifiCount($count['total']);
            $this->navbar->object('forum')->row('reported')->option('post')->notifiCount($count['post']);
            $this->navbar->object('forum')->row('reported')->option('topic')->notifiCount($count['topic']);
            $this->navbar->object('forum')->row('reported')->option('profilepost')->notifiCount($count['profile_post']);
            $this->navbar->object('forum')->row('reported')->option('profilepostcomment')->notifiCount($count['profile_post_comment']);
        }

        if (($count = $deleted->getAllCount()) != 0) {
            $this->navbar->object('forum')->row('deleted')->notifiCount($count);
        }

        $this->page->url = $this->url;
        $this->page->data = $this->data;
        $this->page->style = $this->style;
        $this->page->user = $this->user;
        $this->page->build = $this->build;
        $this->page->navbar = $this->navbar;
        $this->page->system = $this->system;
        $this->page->process = $this->process;
        $this->page->language = $this->language;
        $this->page->template = $this->template;

        $this->page->ini();
        $this->page->body();

        $this->data->navbar = $this->page->navbar->getData();

        $this->iniStyle();
   }

}