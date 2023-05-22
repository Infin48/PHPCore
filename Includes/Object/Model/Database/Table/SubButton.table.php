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
 * SubButton
 */
class SubButton extends Table
{
    /**
     * Returns sub button
     *
     * @param  int $ID Button sub ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_BUTTONS_SUB . '
            WHERE button_sub_id = ?
        ', [$ID]);
    }

    /**
     * Returns all sub buttons from dropdown
     *
     * @param  int $ID Dropdown ID
     * 
     * @return array
     */
    public function parent( int $ID )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_BUTTONS_SUB . '
            WHERE button_id = ?
            ORDER BY position_index DESC
        ', [$ID], ROWS);
    }
}