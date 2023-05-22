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
 * PluginSettings
 */
class PluginSettings
{
    /**
     * @var \App\Plugin\Plugin $plugin Plugin
     */
    private \App\Plugin\Plugin $plugin;

    /**
     * @var string $plugin Plugin name
     */
    private string $name;

    /**
     * Constructor
     * 
     * @param \App\Plugin\Plugin $plugin Plugin name
     * @param string $name Plugin settings
     */
    public function __construct( \App\Plugin\Plugin $plugin, string $name = '' )
    {
        $this->name = $name;
        $this->plugin = $plugin;
    }

    /**
     * Set class of page which logged user visited
     * 
     * @return void
     */
    public function setCurrentPage( string $page )
    {
        if (!$this->name)
        {
            return;
        }

        $this->plugin->settings[$this->name]['visited'] = true;
        $this->plugin->settings[$this->name]['page'] = $page;
    }

    /**
     * Sets settings to plugin
     *
     * @param array $settings Plugin settings
     * 
     * @return void
     */
    public function setPluginSettings( array $settings )
    {
        // Encode to JSON
        $JSON = json_encode($settings);

        // Save to database
        $this->plugin->db->query('UPDATE ' . TABLE_PLUGINS . ' SET plugin_settings = ? WHERE plugin_id = ?', [$JSON, $this->plugin->settings[$this->name]['id']]);
    }

    /**
     * Get data from plugin
     *
     * @param string $key Key to data
     * 
     * @return mixed
     */
    public function get( string $key = null )
    {
        if (is_null($key))
        {
            return  $this->plugin->settings[$this->name];
        }

        $return =  $this->plugin->settings[$this->name];
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
     * Returns true if plugin is installed
     * 
     * @return bool
     */
    public function isInstalled()
    {
        if (isset($this->plugin->settings[$this->name]))
        {
            return true;
        }

        return false;
    }
}