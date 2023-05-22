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
 * Group
 */
class Group extends Table
{    
    /**
     * Returns group
     *
     * @param  int $ID Group ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_GROUPS . ' WHERE group_id = ?', [$ID]);
    }
    
    /**
     * Returns all groups
     *
     * @return array
     */
    public function all()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_GROUPS . ' ORDER BY group_index DESC', [], ROWS);
    }

    /**
     * Returns ID of all groups
     *
     * @return array
     */
    public function getAllID()
    {
        return array_column($this->db->query('SELECT group_id FROM ' . TABLE_GROUPS, [], ROWS), 'group_id');
    }

    /**
     * Returns ID of all groups with visitor group
     *
     * @return array
     */
    public function getAllIDWithVisitor()
    {
        return array_merge(array_column($this->db->query('SELECT group_id FROM ' . TABLE_GROUPS, [], ROWS), 'group_id'), [(int)0]);
    }
    
    /**
     * Returns groups with smaller group index then logged user
     *
     * @return array
     */
    public function less()
    {
        return $this->db->query('
            SELECT group_id, group_name 
            FROM ' . TABLE_GROUPS . ' 
            WHERE group_index < ' . LOGGED_USER_GROUP_INDEX . '
            ORDER BY group_index DESC
        ', [], ROWS);
    }

    /**
     * Returns ID of all groups with smaller index than logged user
     *
     * @return array
     */
    public function getLessID()
    {
        return array_column($this->db->query('
            SELECT group_id, group_name 
            FROM ' . TABLE_GROUPS . ' 
            WHERE group_index < ' . LOGGED_USER_GROUP_INDEX . '
            ORDER BY group_index DESC
        ', [], ROWS), 'group_id');
    }
}