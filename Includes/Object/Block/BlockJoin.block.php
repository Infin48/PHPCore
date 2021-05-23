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
 * BlockJoin
 */
class BlockJoin
{
    /**
     * Returns user join statement
     * 
     * @param string $on On value
     * 
     * @return string
     */
    public function user( string $on )
    {
        return 'LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = ' . $on . ' LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id';
    }
}