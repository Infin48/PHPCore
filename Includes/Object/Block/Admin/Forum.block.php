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
 * Forum
 */
class Forum extends \Block\Forum
{    
    /**
     * Returns forum
     *
     * @param  int $forumID Forum ID
     * 
     * @return array
     */
    public function get( int $forumID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_FORUMS . ' WHERE f.forum_id = ?', [$forumID]);
    }

    /**
     * Returns all forums
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_FORUMS, [], ROWS);
    }
    
    /**
     * Returns ID of all forums
     *
     * @return array
     */
    public function getAllID()
    {
        return array_column($this->db->query('SELECT * FROM ' . TABLE_FORUMS, [], ROWS), 'forum_id');
    }

    /**
     * Returns forums from category
     *
     * @param  int $categoryID Category ID
     * 
     * @return array
     */
    public function getParent( int $categoryID )
    {
        return $this->db->query('
            SELECT f.forum_id, forum_name, forum_url, forum_description, is_main, forum_icon_style, forum_icon
            FROM ' . TABLE_FORUMS . '
            WHERE f.category_id = ?
            GROUP BY f.forum_id
            ORDER BY position_index DESC
        ', [$categoryID], ROWS);
    }

    /**
     * Returns forum statistics
     *
     * @return array
     */
    public function getStats()
    {
        return $this->db->query('
            SELECT (
                SELECT COUNT(*)
                FROM ' . TABLE_POSTS . '
            ) as post, (
                SELECT COUNT(*)
                FROM ' . TABLE_TOPICS . '
            ) as topic, (
                SELECT COUNT(*)
                FROM ' . TABLE_USERS . '
                WHERE u.is_deleted = 0
            ) as user
        ');
    }
}