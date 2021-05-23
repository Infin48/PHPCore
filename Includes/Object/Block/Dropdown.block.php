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
 * Dropdown
 */
class Dropdown extends Block
{    
    /**
     * Returns dropdown
     *
     * @param  int $dropdownID Dropdown ID
     * 
     * @return array
     */
    public function get( int $dropdownID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_BUTTONS . ' WHERE is_dropdown = 1 AND button_id = ?', [$dropdownID]);
    }
}