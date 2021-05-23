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

namespace Block\Admin;

/**
 * Group
 */
class Group extends \Block\Group
{
    /**
     * Returns group
     *
     * @param  int $groupID Group ID
     * 
     * @return array
     */
    public function get( int $groupID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_GROUPS . ' WHERE group_id = ? AND group_index < ?', [$groupID, LOGGED_USER_GROUP_INDEX]);
    }
    
    /**
     * Returns all groups
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_GROUPS . ' ORDER BY group_index DESC', [], ROWS);
    }
}