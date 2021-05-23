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
 * BlockSelect
 */
class BlockSelect
{
    /**
     * Returns pre defined user columns
     * 
     * @return string
     */
    public function user()
    {
        return 'u.user_id, u.user_name, u.user_profile_image, u.is_deleted, u.user_last_activity, g.group_class_name';
    }
}