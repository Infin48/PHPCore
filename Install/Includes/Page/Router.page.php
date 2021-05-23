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

use Model\Data;
use Model\System;
use Model\Language;

use Process\Process;

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
        $this->data = new Data();
        $this->system = new System();
        $this->language = new Language();
        $this->process = new Process();
    }
    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body()
    {
        $this->templateName = 'Body';

        $settings = json_decode(file_get_contents(ROOT . '/Install/Includes/Settings.json'), true);

        $this->language->load('/Languages/' . ($this->system->get('site.language') ?? 'cs') . '/Install');

        $this->data->data([
            'page' => $settings['page']
        ]);

        if (isset($_GET['repeat'])) {

            $this->system->install([
                'db' => false,
                'page' => 0,
            ]);
            redirect('/Install/');
        }

        if (isset($_GET['install'])) {

            define('AJAX', true);

            $this->page = new \Page\Ajax\Installation();
            $this->page->system = $this->system;
            $this->page->language = $this->language;
            $this->page->body();
        }

        define('AJAX', false);

        $this->page = match((int)$settings['page']) {
            0 => new \Page\Index(),
            1 => new \Page\Language(),
            2 => new \Page\Database(),
            3 => new \Page\Installation(),
            4 => new \Page\Admin(),
            5 => new \Page\Site(),
            6 => new \Page\End()
        };
        
        $this->page->data = $this->data;
        $this->page->system = $this->system;
        $this->page->process = $this->process;
        $this->page->language = $this->language;

        $this->page->body();
    }
}