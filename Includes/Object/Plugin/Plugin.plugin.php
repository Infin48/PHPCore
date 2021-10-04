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

namespace Plugin;

use Model\Database\Query;

/**
 * Plugin
 */
class Plugin
{
    /**
     * @var array $plugins Plugins settings
     */
    private static array $plugins = [];

    /**
     * @var string $plugin Plugin name
     */
    private string $plugin;

    /**
     * Constructor
     * 
     * @param  string $name Plugin name
     */
    public function __construct( string $plugin )
    {
        $this->plugin = $plugin;
    }
    
    /**
     * Loads plugin settings
     *
     * @return void
     */
    public function loadSettings()
    {
        define('TABLE_PLUGIN_' . strtoupper($this->plugin), 'phpcore_plugins_' . strtolower($this->plugin));

        $query = new Query();
        $result = $query->query('SELECT * FROM ' . 'phpcore_plugins_' . strtolower($this->plugin), [], ROWS);

        foreach ($result as $option) {
            self::$plugins[$this->plugin . '.' . $option['key']] = $option['value'];
        }
    }
    
    /**
     * Returns plugins settings
     *
     * @return array
     */
    public static function getPlugins()
    {
        return self::$plugins;
    }

    /**
     * Returns plugin visualization
     * 
     * @param string $type Visualization type
     * @param string $path Visualization path
     * 
     * @return \Plugin\PluginVisualization 
     */
    public function visual( string $type, string $path )
    {
        return new PluginVisualization($this->plugin, $type, $path);
    }

    /**
     * Returns plugin installation
     * 
     * @return \Plugin\PluginInstall 
     */
    public function install()
    {
        return new PluginInstall($this->plugin);
    }

    /**
     * Returns plugin initialization
     * 
     * @return \Plugin\PluginInitialization 
     */
    public function ini()
    {
        return new PluginInitialization();
    }

    /**
     * Returns plugin uninstallation
     * 
     * @return \Plugin\PluginUninstall 
     */
    public function uninstall()
    {
        return new PluginUninstall($this->plugin);
    }
}