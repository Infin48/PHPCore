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
use Block\Deleted;

use Visualization\Navbar\Navbar;

/**
 * Router
 */
class Router extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'Header',
        'loggedIn' => true,
        'permission' => 'admin.?'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // LOAD LANGUAGE
        $this->language->load('/Languages/' . $this->system->settings->get('site.language') . '/Admin');

        $controllerName = $this->build();

        if (str_contains($controllerName, 'Page\Admin\Ajax')) {
            define('AJAX', true);
        } else define('AJAX', false);

        $this->page = new $controllerName;
        
        $this->navbar = new Navbar('Admin');
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

        $this->page->initialise();
        $this->page->body();

        if (AJAX) {
            echo json_encode($this->data->data);
            exit();
        }

        $this->data->navbar = $this->page->navbar->getData();
   }

}