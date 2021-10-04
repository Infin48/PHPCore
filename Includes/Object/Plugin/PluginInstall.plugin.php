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
 * PluginInstall
 */
class PluginInstall
{
    /**
     * @var string $plugin Plugins name
     */
    private string $plugin;

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
     * Generates table with plugin settings
     *
     * @param array $data Default plugin settings
     * 
     * @return array
     */
    public function settings( array $data )
    {
        $table = 'phpcore_plugins_' . strtolower($this->plugin);
        $this->db->query('
            CREATE TABLE IF NOT EXISTS `' . $table . '` (
                `key` varchar(225) NOT NULL,
                `value` varchar(225) NOT NULL
            ) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ');

        $query = 'INSERT INTO ' . $table . ' (`key`, `value`) VALUES ';

        $values = [];
        foreach ($data as $key => $value) {
            array_push($values, '("' . $key . '", "' . $value . '")');
        }

        $this->db->query($query . implode(',', $values));
    }

    /**
     * Generates table
     *
     * @param string $table Table name
     * @param string $columns Table columns
     * @param string $key Primary key
     * 
     * @return void
     */
    public function table( string $table, array $columns, string $key = null )
    {
        $query = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (';
                
        $_columns = [];
        foreach ($columns as $column => $settings) {
            array_push($_columns, '`' . $column. '` ' . $settings);
        }
        if (is_null($key) === false) {
            array_push($_columns, 'PRIMARY KEY (`' . $key . '`)');
        }

        $query .= implode(',', $_columns) . ' )DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;';

        $this->db->query($query);
    }
}