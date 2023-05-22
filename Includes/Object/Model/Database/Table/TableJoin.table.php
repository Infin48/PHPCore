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
 * TableJoin
 */
class TableJoin
{
    /**
     * Returns user join statement
     * 
     * @param  string $on On value
     * @param  bool $role If true - Tables with roles will be joined
     * 
     * @return string
     */
    public function user( string $on, bool $role = false )
    {
        return '
        LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = ' . $on . '
        LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
        ' . ($role ? '
        LEFT JOIN ' . TABLE_ROLES . ' ON ro.role_id = (
            SELECT role_id
            FROM ' . TABLE_ROLES . 'l
            WHERE FIND_IN_SET(rol.role_id, u.user_roles)
            ORDER BY rol.position_index DESC
            LIMIT 1
        )' : '');
    }
}