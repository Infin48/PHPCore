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
 * PluginUninstall
 */
class PluginUninstall
{
    /**
     * @var \Model\Database\Query $db Query
     */
    private \Model\Database\Query $db;

    /**
     * Constructor
     * 
     * @param  string $name Plugin name
     */
    public function __construct( string $plugin )
    {
        $this->plugin = $plugin;

        $this->db = new Query();
    }
    
    /**
     * Drops table
     *
     * @param array $table Table name
     * 
     * @return void
     */
    public function drop( string|array $table )
    {
        if (is_array($table)) {
            foreach ($table as $_table) {
                $this->db->query('DROP TABLE IF EXISTS `' . $_table . '`');
            }
        } else {
            $this->db->query('DROP TABLE IF EXISTS `' . $table . '`');
        }
    }

    /**
     * Drops table with plugin settings
     * 
     * @return void
     */
    public function dropSettings()
    {
        $this->db->query('DROP TABLE IF EXISTS `phpcore_plugins_' . strtolower($this->plugin) . '`');
    }
}