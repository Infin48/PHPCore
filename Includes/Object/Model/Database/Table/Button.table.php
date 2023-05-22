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
 * Button
 */
class Button extends Table
{   
    /**
     * Returns button
     *
     * @param  int $ID Button ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_BUTTONS . '
            WHERE button_id = ?
        ', [$ID]);
    }
    
    /**
     * Returns all buttons 
     *
     * @return array
     */
    public function all()
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_BUTTONS . '
            ORDER By position_index DESC
        ', [], ROWS);
    }
}