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

namespace Block;

/**
 * Plugin
 */
class Plugin extends Block
{    
    /**
     * Returns plugin
     *
     * @param  int $pluginID Plugin ID
     * 
     * @return array
     */
    public function get( int $pluginID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_PLUGINS . ' WHERE plugin_id = ?', [$pluginID]);
    }

    /**
     * Returns plugin settings
     *
     * @param  string $pluginName Plugin name
     * 
     * @return array
     */
    public function getSettings( string $pluginName )
    {
        $table = 'phpcore_plugins_' . strtolower($pluginName);

        if ($this->db->query('SHOW TABLES LIKE "' . $table . '"')) {

            $settings = $this->db->query('SELECT * FROM ' . $table, [], ROWS);
        } else {
            
            return [];
        }

        if (isset($settings[0]['key']) and isset($settings[0]['value'])) {
            $data = [];
            foreach ($settings as $setting) {
                $data[$setting['key']] = $setting['value'];
            }
            return $data;
        }

        return [];
    }
    
    /**
     * Returns all installed plugins
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_PLUGINS, [], ROWS);
    }
    
    /**
     * Returns ID of all installed plugins
     *
     * @return array
     */
    public function getAllID()
    {
        return array_column($this->db->query('SELECT * FROM ' . TABLE_PLUGINS, [], ROWS), 'plugin_id');
    }
}