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

namespace App\Plugin;

/**
 * Plugin
 */
class Plugin
{
    /**
     * @var array $settings Plugins settings
     */
    public array $settings = [];

    /**
     * @var \App\Model\Database\Query $db Query compiler
     */
    public \App\Model\Database\Query $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new \App\Model\Database\Query();
    }

    /**
     * Finds plugin by name
     *
     * @param string $pluginName Plugin name
     * 
     * @return \Plugin\PluginSettings
     */
    public function findByName( string $pluginName )
    {
        // Set plugin
        return new \App\Plugin\PluginSettings($this, $pluginName);
    }

    /**
     * Finds plugin by ID
     *
     * @param int $pluginID Plugin ID
     * 
     * @return \App\Plugin\PluginSettings
     */
    public function findByID( int $pluginID )
    {
        // Foreach every installed plugin
        foreach ($this->settings as $pluginName => $settings)
        {
            if ($settings['id'] == $pluginID)
            {
                // Set plugin
                return new \App\Plugin\PluginSettings($this, $pluginName);
            }
        }

        // Set plugin
        return new \App\Plugin\PluginSettings($this, '');
    }
    
    /**
     * Loads installed plugins
     *
     * @return void
     */
    public function loadInstalledPlugins()
    {
        // File model
        $file = new \App\Model\File\File();

        // Model for translating paths
        $path = new \App\Model\Path();

        // Foreach every installed plugin
        foreach ($this->db->select('app.plugin.all()') as $item)
        {
            // If this plugin is already loaded
            if (isset($this->settings[$item['plugin_name_folder']]))
            {
                continue;
            }

            // Build path to initialize file
            $_ini = '/Plugins/' . $item['plugin_name_folder'] . '/Ini.plugin.php';
            
            // If initialization file exists
            if ($file->exists($_ini))
            {
                // Load file
                require $path->build($_ini);
            }

            // Load JSON information about plugin
            $JSON = new \App\Model\File\JSON('/Plugins/' . $item['plugin_name_folder'] . '/Info.json');
            
            // Plugin informations & settings
            $data = [];
            
            // If JSON exists
            if ($JSON->exists())
            {
                // Save JSON information
                $data = $JSON->get();
            }

            // Set default value to false
            // This value will be true if logged user enters to page which is from plugin
            $data['visited'] = false;

            // Here will be saved current visited plugin page
            $data['page'] = '';

            // Here will be plugin settings from database
            $data['settings'] = json_decode($item['plugin_settings'] ?: '{}', true);

            // Save list of tables which were created due to this plugin
            $data['tables'] = array_filter(explode(',', $item['plugin_tables'] ?? ''));

            // Save plugin ID
            $data['id'] = $item['plugin_id'];

            // Save plugin template
            $data['template'] = $item['plugin_template'];

            // Save plugin folder name
            $data['folder'] = $item['plugin_name_folder'];

            // Save plugin language
            $data['language'] = $item['plugin_language'];

            // Store plugin information and setting to class
            $this->settings[$item['plugin_name_folder']] = $data;
        }

        if (!defined('LIST_OF_INSTALLED_PLUGINS'))
        {
            define('LIST_OF_INSTALLED_PLUGINS', array_values(array_keys($this->settings)));
        }
    }

    /**
     * Returns plugin data
     * 
     * @param string $key Key to data
     * 
     * @return mixed
     */
    public function get( string $key = null )
    {
        $return = $this->settings;

        if (is_null($key))
        {
            return $return;
        }

        foreach (explode('.', $key) as $_key)
        {
            if (!isset($return[$_key]))
            {
                return '';
            }

            $return = $return[$_key];
        }

        return $return;
    }

    /**
     * Sets value to plugin
     * 
     * @param string $key Key to data
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $path = '';

        $keys = preg_split('/(?<=[a-zA-Z0-9-_\/\[\]])[.]/', $key);

        foreach ($keys as $_key)
        {
            $path .= '[\'' . str_replace('\.', '.', $_key) . '\']';
        }

        eval('$this->settings' . $path . ' = $value;');
    }
}