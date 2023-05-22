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

namespace App\Table;

/**
 * Page
 */
class Page extends Table
{    
    /**
     * Returns custom page
     *
     * @param  int $ID Custom page ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_PAGES . ' WHERE page_id = ?', [$ID]);
    }
    
    /**
     * Returns all custom pages
     *
     * @return array
     */
    public function all()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_PAGES, [], ROWS);
    }
    
    /**
     * Returns ID of all custom pages
     *
     * @return array
     */
    public function getAllID()
    {
        return array_column($this->db->query('SELECT * FROM ' . TABLE_PAGES, [], ROWS), 'page_id');
    }
}