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

namespace App\Model;

/**
 * Data
 */
class Data 
{
    /**
     * @var array $data Page data
     */
    public array $data = [
        'previous' => 'Page\Router'
    ];

    /**
     * @var \App\Visualization\VisualizationGenerate $block Block data
     */
    public \App\Visualization\VisualizationGenerate $block;

    /**
     * @var \App\Visualization\VisualizationGenerate $form Form data
     */
    public \App\Visualization\VisualizationGenerate $form;

    /**
     * @var \App\Visualization\VisualizationGenerate $list List data
     */
    public \App\Visualization\VisualizationGenerate $list;

    /**
     * @var object $d Additional data
     */
    public object $d;

    /**
     * @var array $head Head data
     */
    public array $head = [];

    /**
     * @var string $chart Chart data
     */
    public string $chart = '';

    /**
     * @var string $notice Notice message
     */
    public string $notice = '';

    /**
     * @var \App\Visualization\VisualizationGenerate $panel Panel data
     */
    public \App\Visualization\VisualizationGenerate $panel;
    
    /**
     * @var \App\Visualization\VisualizationGenerate $navbar Navbar data
     */
    public \App\Visualization\VisualizationGenerate $navbar;

    /**
     * @var \App\Visualization\VisualizationGenerate $sidebar Sidebar data
     */
    public \App\Visualization\VisualizationGenerate $sidebar;

    /**
     * @var \App\Visualization\VisualizationGenerate $breadcrumb Breadcrumb data
     */
    public \App\Visualization\VisualizationGenerate $breadcrumb;

    /**
     * @var \App\Visualization\VisualizationGenerate $notification Notification data
     */
    public \App\Visualization\VisualizationGenerate $notification;

    /**
     * @var array $pagination Pagination data
     */
    public array $pagination = [];

    /**
     * @var array $message Message
     */
    public array $message = [];

    /**
     * @var bool $header If true - big header will be showed
     */
    public bool $header = false;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    public bool $editor = false;

    /**
     * @var bool $plugin If true - User entered to plugin page
     */
    public bool $plugin = false;

    public function __construct()
    {
        $plugin = new \App\Plugin\Plugin();
        $plugin->loadInstalledPlugins();

        $system = new \App\Model\System();
        
        $trumbowyg = new \App\Model\Trumbowyg( $system->get('site.language_editor') );

        $this->d = new \App\Visualization\Visualization([
            'options' => [
                'header' => false,
                'editor' => false,
                'plugin' => false,
                'photoSwipe' => false,
                'adminAccess' => false,
                'profileHeader' => false,
                'setupTemplate' => false
            ],
            'data' => [
                'mentionUserList' => json_encode([]),
                'head' => [],
                'trumbowyg' => [
                    'big' => $trumbowyg->big(),
                    'small' => $trumbowyg->small()
                ]
            ],
            'inst' => [
                'plugin' => $plugin,
                'user' => new \App\Model\User(),
                'system' => $system,
                'language' => new \App\Model\Language( $plugin )
            ]
        ]);

        $this->list = (new \App\Visualization\Visualization())->getDataToGenerate();
        $this->form = (new \App\Visualization\Visualization())->getDataToGenerate();
        $this->block = (new \App\Visualization\Visualization())->getDataToGenerate();
        $this->panel = (new \App\Visualization\Visualization())->getDataToGenerate();
        $this->navbar = (new \App\Visualization\Visualization())->getDataToGenerate();
        $this->sidebar = (new \App\Visualization\Visualization())->getDataToGenerate();
        $this->breadcrumb = (new \App\Visualization\Visualization())->getDataToGenerate();
        $this->notification = (new \App\Visualization\Visualization())->getDataToGenerate();
    }

    /**
     * Sets data
     *
     * @param string $key
     * @param mixed $value
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $this->d->set($key, $value);
    }

    /**
     * Gets data
     *
     * @param string $key
     * 
     * @return mixed
     */
    public function get( string $key)
    {
        return $this->d->get($key);
    }

    /**
     * Eachs data
     *
     * @param string $key
     * @param callable $function
     * 
     * @return mixed
     */
    public function each( string $key, callable $function )
    {
        return $this->d->each($key, $function);
    }
}
