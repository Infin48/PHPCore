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
 * Forum
 */
class Forum extends Block
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
        return $this->db->query('
            SELECT f.forum_id, f.forum_link, forum_name, forum_url, forum_description, forum_posts, forum_topics, is_main, category_name, c.category_id, forum_icon_style, forum_icon,
                CASE WHEN fpt.forum_id IS NOT NULL THEN 1 ELSE 0 END as topic_permission
            FROM ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_CATEGORIES . ' ON c.category_id = f.category_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = c.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_TOPIC . ' ON fpt.forum_id = f.forum_id AND fpt.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE f.forum_id = ? AND cps.category_id IS NOT NULL AND fps.forum_id IS NOT NULL
            GROUP BY f.forum_id
        ', [$forumID]);
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
            SELECT t.*, f.*, f.forum_id, ' . $this->select->user() . '
            FROM ' . TABLE_FORUMS . ' 
            LEFT JOIN (
                SELECT t.topic_id, t.forum_id, t.topic_name, t.topic_url, CASE WHEN t2.topic_created < p2.post_created THEN p2.user_id ELSE t2.user_id END AS user_id, CASE WHEN t2.topic_created < p2.post_created THEN p2.post_created ELSE t2.topic_created END AS created
                FROM ' . TABLE_TOPICS . '
                LEFT JOIN ' . TABLE_POSTS . '2 ON p2.post_id = (
                    SELECT MAX(post_id)
                    FROM ' . TABLE_POSTS . '3
                    LEFT JOIN ' . TABLE_TOPICS . '3 ON t3.topic_id = p3.topic_id
                    WHERE p3.forum_id = t.forum_id AND p3.deleted_id IS NULL AND t3.deleted_id IS NULL
                )
                LEFT JOIN ' . TABLE_TOPICS . '2 ON t2.topic_id = ( SELECT MAX(topic_id) FROM ' . explode(' ', TABLE_TOPICS)[0] . ' WHERE forum_id = t.forum_id AND deleted_id IS NULL)
                WHERE t.topic_id = CASE WHEN t2.topic_created < p2.post_created THEN p2.topic_id ELSE t2.topic_id END AND t.deleted_id IS NULL
            ) t ON t.forum_id = f.forum_id
            ' . $this->join->user('t.user_id') . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = f.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE f.category_id = ? AND fps.forum_id IS NOT NULL AND cps.category_id IS NOT NULL
            GROUP BY f.forum_id
            ORDER BY position_index DESC
        ', [$categoryID], ROWS);
    }

    /**
     * Returns all forums
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('
            SELECT f.forum_id, forum_name, forum_url, forum_description, is_main
            FROM ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = f.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE fps.forum_id IS NOT NULL AND cps.category_id IS NOT NULL
            GROUP BY f.forum_id
        ', [], ROWS);
    }

    /**
     * Returns all forums where topics can be moved
     * 
     * @param int $forumID ID of forum which will be ignored
     *
     * @return array
     */
    public function getAllToMove( int $forumID )
    {
        return $this->db->query('
            SELECT f.forum_id, forum_name, forum_url, forum_description, is_main
            FROM ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_TOPIC . ' ON fpt.forum_id = f.forum_id AND fpt.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = f.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE fps.forum_id IS NOT NULL AND cps.category_id IS NOT NULL AND fpt.forum_id IS NOT NULL AND f.forum_id != ? AND f.forum_link = ""
            GROUP BY f.forum_id
        ', [$forumID], ROWS);
    }
    
    /**
     * Returns ID of all groups which has permission to see forum
     *
     * @param  int $forumID Forum ID
     * 
     * @return array
     */
    public function getSee( int $forumID )
    {
        return array_column($this->db->query('SELECT group_id FROM ' . TABLE_FORUMS_PERMISSION_SEE . ' WHERE forum_id = ?', [$forumID], ROWS), 'group_id');
    }
    
    /**
     * Returns ID of all groups which has permission to create post in forum
     *
     * @param  int $forumID Forum ID
     * 
     * @return array
     */
    public function getPost( int $forumID )
    {
        return array_column($this->db->query('SELECT group_id FROM ' . TABLE_FORUMS_PERMISSION_POST . ' WHERE forum_id = ?', [$forumID], ROWS), 'group_id');
    }
    
    /**
     * Returns ID of all groups which has permission to create topic in forum
     *
     * @param  int $forumID Forum ID
     * 
     * @return array
     */
    public function getTopic( int $forumID )
    {
        return array_column($this->db->query('SELECT group_id FROM ' . TABLE_FORUMS_PERMISSION_TOPIC . ' WHERE forum_id = ?', [$forumID], ROWS), 'group_id');
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
                LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = p.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = f.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
                WHERE p.deleted_id IS NULL AND cps.category_id IS NOT NULL AND fps.forum_id IS NOT NULL
            ) as post, (
                SELECT COUNT(*)
                FROM ' . TABLE_TOPICS . '
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = t.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = t.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
                WHERE t.deleted_id IS NULL AND cps.category_id IS NOT NULL AND fps.forum_id IS NOT NULL
            ) as topic, (
                SELECT COUNT(*)
                FROM ' . TABLE_USERS . '
                WHERE u.is_deleted = 0
            ) as user
        ');
    }
}