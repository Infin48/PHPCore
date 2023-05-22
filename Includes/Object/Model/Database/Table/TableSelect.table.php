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
 * TableSelect
 */
class TableSelect
{
    /**
     * Returns pre defined user columns
     * 
     * @param  bool $role IF true - Columns from roles table will be selected
     * 
     * @return string
     */
    public function user( bool $role = false )
    {
        return 'u.user_id, u.user_name, u.user_profile_image, u.user_deleted, u.user_last_activity, u.user_reputation, g.group_name, g.group_class, u.user_signature' . ($role ? ', ro.role_class, ro.role_icon, ro.role_name, ro.role_color' : '');
    }
}