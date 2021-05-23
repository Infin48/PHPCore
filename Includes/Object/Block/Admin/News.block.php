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
 * News
 */
class News extends \Block\Block
{
    /**
     * Returns all news
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('
            SELECT deleted_id, topic_id, topic_url, topic_image, topic_views, topic_text, topic_name, topic_created, topic_posts, is_locked, is_sticky, ' . $this->select->user() . ', is_sticky, user_last_activity
            FROM ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            ' . $this->join->user('t.user_id'). '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = f.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE f.is_main = 1 AND fps.forum_id IS NOT NULL AND cps.category_id IS NOT NULL
            ORDER BY is_sticky DESC, topic_id DESC
            LIMIT ?, ?
        ',[$this->pagination['offset'], $this->pagination['max']], ROWS);
    }

    /**
     * Returns count of news
     * 
     * @return int
     */
    public function getAllCount()
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = f.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE f.is_main = 1 AND fps.forum_id IS NOT NULL AND cps.category_id IS NOT NULL
        ')['count'];
    }
}