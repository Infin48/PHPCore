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
 * Role
 */
class Role extends Table
{    
    /**
     * Returns role
     *
     * @param  int $ID Role ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_ROLES . '
            WHERE role_id = ?
        ', [$ID]);
    }
    
    /**
     * Returns all roles
     *
     * @return array
     */
    public function all()
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_ROLES . '
            ORDER BY position_index DESC    
        ', [], ROWS);
    }

    /**
     * Returns all roles
     *
     * @param  string $ID ID of searched roles
     * 
     * @return array
     */
    public function parent( string $ID )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_ROLES . '
            WHERE role_id IN (' . ($ID ?: '""') . ')
            ORDER BY position_index DESC
        ', [], ROWS);
    }
}