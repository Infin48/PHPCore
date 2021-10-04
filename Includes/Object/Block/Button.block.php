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
 * Button
 */
class Button extends Block
{   
    /**
     * Returns button
     *
     * @param  int $buttonID Button ID
     * 
     * @return array
     */
    public function get( int $buttonID )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_BUTTONS . '
            WHERE button_id = ?
        ', [$buttonID]);
    }
    
    /**
     * Returns all buttons 
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_BUTTONS . '
            ORDER By position_index DESC
        ', [], ROWS);
    }
}