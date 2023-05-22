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
 * Forum
 */
class Forum extends Table
{
    /**
     * Returns forum
     *
     * @param  int $ID Forum ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        $forum = $this->db->query('
            SELECT f.forum_id, f.forum_link, forum_name, forum_url, forum_description, forum_posts, forum_topics, forum_main, category_name, c.category_id, forum_icon, cp.*, fp.*, cp.permission_see as permission_see_category, fp.permission_see AS permission_see_forum
            FROM ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_CATEGORIES . ' ON c.category_id = f.category_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = c.category_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
            WHERE f.forum_id = ?
            GROUP BY f.forum_id
        ', [$ID]);

        if (!$forum)
        {
            return [];
        }

        $forum['permission_see_forum_'] = $forum['permission_see_forum'] = explode(',', $forum['permission_see_forum']);
        $forum['permission_see_category'] = explode(',', $forum['permission_see_category']);

        if (in_array('*', $forum['permission_see_forum']))
        {
            $forum['permission_see_forum_'] = $forum['permission_see_category'];
        }

        if (in_array('*', $forum['permission_see_category']))
        {
            $forum['permission_see_category'] = $forum['permission_see_forum_'];
        }

        $forum['permission_see'] = array_intersect($forum['permission_see_forum_'], $forum['permission_see_category']);
        $forum['permission_post'] = explode(',', $forum['permission_post']) ?: [];
        $forum['permission_topic'] = explode(',', $forum['permission_topic']) ?: [];

        return $forum;
    }

    /**
     * Returns main forum
     * 
     * @return array
     */
    public function getMain()
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_CATEGORIES . ' ON c.category_id = f.category_id
            WHERE f.forum_main = 1
        ');
    }

    /**
     * Returns forums from category
     *
     * @param  int $ID Category ID
     * @param  bool $deleted If true last deleted post will be returned
     * 
     * @return array
     */
    public function parent( int $ID, bool $deleted = false )
    {
        $forums = $this->db->query('
            SELECT t.*, f.*, f.forum_id, fp.*, ' . $this->select->user(role: true) . '
            FROM ' . TABLE_FORUMS . ' 
            LEFT JOIN (
                SELECT IFNULL(t.deleted_id, p2.deleted_id) as deleted_id, t.topic_id, t.forum_id, t.topic_name, t.topic_url, CASE WHEN t2.topic_created < p2.post_created THEN p2.user_id ELSE t2.user_id END AS user_id, CASE WHEN t2.topic_created < p2.post_created THEN p2.post_created ELSE t2.topic_created END AS created
                FROM ' . TABLE_TOPICS . '
                LEFT JOIN ' . TABLE_POSTS . '2 ON p2.post_id = (
                    SELECT MAX(post_id)
                    FROM ' . TABLE_POSTS . '3
                    LEFT JOIN ' . TABLE_TOPICS . '3 ON t3.topic_id = p3.topic_id
                    WHERE p3.forum_id = t.forum_id  ' . ($deleted === false ? 'AND p3.deleted_id IS NULL AND t3.deleted_id IS NULL' : '') . '
                )
                LEFT JOIN ' . TABLE_TOPICS . '2 ON t2.topic_id = ( SELECT MAX(topic_id) FROM ' . explode(' ', TABLE_TOPICS)[0] . ' WHERE forum_id = t.forum_id ' . ($deleted === false ? 'AND deleted_id IS NULL' : '') . ')
                WHERE t.topic_id = CASE WHEN t2.topic_created < p2.post_created THEN p2.topic_id ELSE t2.topic_id END ' . ($deleted === false ? 'AND t.deleted_id IS NULL' : '') . '
            ) t ON t.forum_id = f.forum_id
            ' . $this->join->user(on: 't.user_id', role: true) . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
            WHERE f.category_id = ? 
            GROUP BY f.forum_id
            ORDER BY position_index DESC
        ', [$ID], ROWS);

        foreach ($forums as &$forum)
        {
            $forum['permission_see'] = explode(',', $forum['permission_see']);
            $forum['permission_post'] = explode(',', $forum['permission_post']);
            $forum['permission_topic'] = explode(',', $forum['permission_topic']);

            $forum['labels'] = [];
            if ($forum['topic_id'])
            {
                $forum['labels'] = $this->getLabels($forum['topic_id']);
            }
        }

        return $forums;
    }

    /**
     * Returns labels from topic
     *
     * @param  int $ID Topic ID
     * 
     * @return array
     */
    public function getLabels( int $ID )
    {
        return $this->db->query('
            SELECT label_name, label_class, l.label_id
            FROM ' . TABLE_TOPICS_LABELS . '
            LEFT JOIN ' . TABLE_LABELS . ' ON l.label_id = tlb.label_id
            WHERE tlb.topic_id = ?
            ORDER BY l.position_index DESC
        ', [$ID], ROWS);
    }

    /**
     * Returns all forums
     * 
     * @param  int $ignoreForumID Given forum will not be returned
     *
     * @return array
     */
    public function all( int $ignoreForumID = null)
    {
        $forums =  $this->db->query('
            SELECT f.forum_id, forum_name, forum_url, forum_description, f.forum_link, forum_main, fp.*, fp.permission_see AS permission_see_forum, cp.permission_see AS permission_see_category
            FROM ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = f.category_id
            ' . ($ignoreForumID ? 'WHERE f.forum_id <> ' . $ignoreForumID : '') . '
            GROUP BY f.forum_id
        ', [], ROWS);

        foreach ($forums as $i => &$forum)
        {
            $forum['permission_see_forum'] = explode(',', $forum['permission_see_forum']);
            $forum['permission_see_category'] = explode(',', $forum['permission_see_category']);
            
            if (in_array('*', $forum['permission_see_forum']))
            {
                $forum['permission_see_forum'] = $forum['permission_see_category'];
            }
    
            if (in_array('*', $forum['permission_see_category']))
            {
                $forum['permission_see_category'] = $forum['permission_see_forum'];
            }
        

            $forum['permission_see'] = array_intersect($forum['permission_see_forum'], $forum['permission_see_category']);

            $forum['permission_post'] = explode(',', $forum['permission_post']);
            $forum['permission_topic'] = explode(',', $forum['permission_topic']);
        }

        return $forums;
    }

    /**
     * Returns forum permissions
     *
     * @param  int $forumID Forum ID
     * 
     * @return array
     */
    public function permission( int $forumID )
    {
        $forum = $this->db->query('SELECT * FROM phpcore_forums_permission WHERE forum_id = ?', [$forumID]);

        $forum['permission_see'] = explode(',', $forum['permission_see']);
        $forum['permission_post'] = explode(',', $forum['permission_post']);
        $forum['permission_topic'] = explode(',', $forum['permission_topic']);

        return $forum;
    }

    /**
     * Returns forum statistics
     *
     * @return array
     */
    public function stats()
    {
        return $this->db->query('
            SELECT (
                SELECT COUNT(*)
                FROM ' . TABLE_POSTS . '
                LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
                LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id
                WHERE p.deleted_id IS NULL AND t.deleted_id IS NULL AND ((FIND_IN_SET(' . LOGGED_USER_GROUP_ID . ', fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET(' . LOGGED_USER_GROUP_ID . ', cp.permission_see) OR FIND_IN_SET("*", cp.permission_see)))
            ) as post, (
                SELECT COUNT(*)
                FROM ' . TABLE_TOPICS . '
                LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id
                WHERE t.deleted_id IS NULL AND ((FIND_IN_SET(' . LOGGED_USER_GROUP_ID . ', fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET(' . LOGGED_USER_GROUP_ID . ', cp.permission_see) OR FIND_IN_SET("*", cp.permission_see)))
            ) as topic, (
                SELECT COUNT(*)
                FROM ' . TABLE_USERS . '
                WHERE u.user_deleted = 0
            ) as user
        ');
    }

    /**
     * Returns all forums except main forum.
     * Forums will be returned without permissions.
     * 
     * @param  int $ignoreForumID Given forum will not be returned
     *
     * @return array
     */
    public function withoutMainForum( int $ignoreForumID = null )
    {
        return $this->db->query('
            SELECT f.forum_id, forum_name, forum_url, forum_description, forum_main, fp.*
            FROM ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
            WHERE f.forum_main = 0 AND forum_link = ""' . ($ignoreForumID ? ' AND f.forum_id <> ' . $ignoreForumID : '') . '
            GROUP BY f.forum_id
        ', [], ROWS);
    }
}