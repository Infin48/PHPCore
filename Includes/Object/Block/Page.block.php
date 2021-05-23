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
 * Page
 */
class Page extends Block
{    
    /**
     * Returns custom page
     *
     * @param  int $pageID Custom page ID
     * 
     * @return array
     */
    public function get( int $pageID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_PAGES . ' WHERE page_id = ?', [$pageID]);
    }
    
    /**
     * Returns all custom pages
     *
     * @return array
     */
    public function getAll()
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