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

use Block\Plugin as BlockPlugin;

use Visualization\Breadcrumb\Breadcrumb;

/**
 * Plugin 
 */
class Plugin extends Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'template' => '~/Includes/Template/Plugin'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BLOCK
        $plugin = new BlockPlugin();
        
        $data = $plugin->get($this->url->getID()) or $this->error();
        $this->data->data['plugin'] = $data;

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        $page = 'Page\Plugin\Plugins\Plugin' . str_replace('Page', '', $this->build(ROOT . '/Plugins/' . $data['plugin_name_folder'] . '/Object/Page/Plugin/'));
        $pageExploded = array_filter(explode('\\', $page));

        $this->data->data['pageName'] = 'plugin plugin-' . strtolower($data['plugin_name_folder']) . ' plugin-' . strtolower($data['plugin_name_folder']) . '-' . strtolower($pageExploded[4]); 

        $this->page = new $page;
        $this->page->url = $this->url;
        $this->page->data = $this->data;
        $this->page->user = $this->user;
        $this->page->style = $this->style;
        $this->page->build = $this->build;
        $this->page->system = $this->system;
        $this->page->process = $this->process;
        $this->page->language = $this->language;
        $this->page->template = $this->template;

        $this->page->ini();
        $this->page->body();
    }
}