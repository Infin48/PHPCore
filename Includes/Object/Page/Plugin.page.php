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
 * Plugin 
 */
class Plugin extends Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Plugin.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        $data->set('options.plugin', true);
        
        if (!$this->url->getID())
        {
            $this->error404(); 
        }

        // Plugin
        $plugin = $data->get('inst.plugin');

        // Laod plugin
        $plugin = $plugin->findByID($this->url->getID());

        // If plugin is not installed
        if (!$plugin->isInstalled())
        {
            // Show error page
            $this->error404();
        }

        $data->set('data.plugin', $plugin->get());

        // Define plugin root
        define('PLUGIN_ROOT', '/plugin/' . $plugin->get('id') . '/');

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Index.json');
        $breadcrumb->create()->jumpTo()->title($plugin->get('name'))->href('/plugin/' . $plugin->get('settings.plugin_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        $page = $this->buildPage(
            path: '/Plugins/' . $plugin->get('folder') . '/Object/Page/Plugin',
            object: 'Plugin\\' . $plugin->get('folder') . '\Page\Plugin'
        );

        $page->body( $data, $db );
        $page->checkForAjax();
    }
}