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
 * Dropdown
 */
class Dropdown extends Table
{    
    /**
     * Returns dropdown
     *
     * @param  int $ID Dropdown ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_BUTTONS . ' WHERE button_dropdown = 1 AND button_id = ?', [$ID]);
    }
}