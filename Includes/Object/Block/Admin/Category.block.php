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

namespace Block\Admin;

/**
 * Category
 */
class Category extends \Block\Category {

    /**
     * Returns category
     * 
     * @param int $categoryID Category ID
     *
     * @return array
     */
    public function get( int $categoryID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_CATEGORIES . ' WHERE category_id = ?', [$categoryID]);
    }

    /**
     * Returns ID of all categories
     * 
     * @return array
     */
    public function getAllID()
    {
        return array_column($this->db->query('SELECT * FROM ' . TABLE_CATEGORIES . ' ORDER BY position_index DESC', [], ROWS), 'category_id');
    }

    /**
     * Returns all categories
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_CATEGORIES . ' ORDER BY position_index DESC', [], ROWS);
    }
}
