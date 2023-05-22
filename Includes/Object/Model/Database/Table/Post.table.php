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
 * Post
 */
class Post extends Table
{
    /**
     * Returns post 
     *
     * @param  int $ID Post ID
     * @param  bool $deleted If true - also deleted post will be returned
     * 
     * @return array
     */
    public function get( int $ID, bool $deleted = false )
    {
        $post = $this->db->query('
            SELECT p.*, fp.*, cp.*, fp.permission_see as permission_see_forum, f.forum_main, ' . $this->select->user(role: true) . ', cp.permission_see AS permission_see_category, t.topic_id, t.topic_locked, t.topic_url, t.topic_name, t.topic_posts, p.deleted_id AS deleted_id_post, t.deleted_id AS deleted_id_topic,
                (SELECT COUNT(*) FROM ' . TABLE_POSTS . '2 WHERE p2.post_id <= p.post_id AND p2.topic_id = p.topic_id ' . ($deleted === false ? 'AND p2.deleted_id IS NULL' : '') . ') AS position,
                r.report_status, ( SELECT COUNT(*) FROM ' . TABLE_POSTS_LIKES . ' WHERE post_id = p.post_id ) AS count_of_likes
            FROM ' . TABLE_POSTS . ' 
            ' . $this->join->user(on: 'p.user_id', role: true). '
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = p.report_id
            LEFT JOIN ' . TABLE_POSTS_LIKES . ' ON pl.post_id = p.post_id
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_Id = t.forum_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id
            WHERE p.post_id = ? ' . ($deleted === false ? 'AND p.deleted_id IS NULL' : '') . '
        ', [$ID]);

        if (!$post)
        {
            return [];
        }

        $post['permission_see_forum'] = explode(',', $post['permission_see_forum']);
        $post['permission_see_category'] = explode(',', $post['permission_see_category']);

        if (in_array('*', $post['permission_see_forum']))
        {
            $post['permission_see_forum'] = $post['permission_see_category'];
        }

        if (in_array('*', $post['permission_see_category']))
        {
            $post['permission_see_category'] = $post['permission_see_forum'];
        }

        $post['permission_see'] = array_intersect($post['permission_see_category'], $post['permission_see_forum']);
        $post['permission_post'] = explode(',', $post['permission_post']);
        $post['permission_topic'] = explode(',', $post['permission_topic']);

        $post['likes'] = [];
        if ($post['count_of_likes'] >= 1)
        {
            $post['likes'] = $this->getLikes($ID);
        }

        return $post;
    }
    
    /**
     * Returns posts from topic
     *
     * @param  int $ID Topic ID
     * @param  bool $deleted If true - also deleted posts will be returned
     * 
     * @return array
     */
    public function parent( int $ID, bool $deleted = false )
    {
        $posts = $this->db->query('
            SELECT p.*, t.topic_name, ' . $this->select->user(role: true) . ', user_posts, user_topics, group_name, user_reputation,
                CASE WHEN pl.post_id IS NULL THEN 0 ELSE 1 END AS is_like, t.topic_name, r.report_status,
                ( SELECT COUNT(*) FROM ' . TABLE_POSTS_LIKES . ' WHERE post_id = p.post_id ) AS count_of_likes
            FROM ' . TABLE_POSTS . '
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = p.report_id
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            ' . $this->join->user(on: 'p.user_id', role: true). '
            LEFT JOIN ' . TABLE_POSTS_LIKES . ' ON pl.post_id = p.post_id
            WHERE p.topic_id = ? ' . ($deleted === false ? 'AND p.deleted_id IS NULL' : '') . '
            GROUP BY p.post_id 
            ORDER BY post_created ASC 
            LIMIT ?, ?
        ', [$ID, $this->pagination['offset'], $this->pagination['max']], ROWS);

        foreach ($posts as &$_)
        {
            $_['likes'] = [];
            if ($_['count_of_likes'] >= 1)
            {
                $_['likes'] = $this->getLikes($_['post_id']);
            }
        }

        return $posts;
    }

    /**
     * Returns number of posts in topic
     *
     * @param  int $ID Topic ID
     * @param  bool $deleted If true - also deleted content will be counted
     * 
     * @return int
     */
    public function parentCount( int $ID, bool $deleted = false )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_POSTS . '
            WHERE topic_id = ? ' . ($deleted === false ? 'AND p.deleted_id IS NULL' : '') . '
        ', [$ID])['count'];
    }

    /**
     * Returns last added posts
     * 
     * @param  int $number Number of posts
     * @param  bool $deleted If true - also deleted content will be returned
     *
     * @return array
     */
    public function last( int $number = 5, bool $deleted = false )
    {
        $posts = $this->db->query('
            SELECT ( SELECT COUNT(*) FROM ' . TABLE_TOPICS_LABELS . ' WHERE topic_id = t.topic_id ) AS count_of_labels, t.deleted_id, t.topic_id, topic_name, topic_locked, topic_sticked, topic_url, t.forum_id, post_id, ' . $this->select->user(role: true) . ', CASE WHEN post_created > topic_created THEN post_created ELSE topic_created END AS created, fp.*, cp.permission_see AS permission_see_category,
                (SELECT COUNT(*) FROM ' . TABLE_POSTS . '2 WHERE p2.post_id <= p.post_id AND p2.topic_id = p.topic_id ' . ($deleted === false ? 'AND p2.deleted_id IS NULL' : '') . ') AS position, IFNULL(t.deleted_id, p.deleted_id) AS deleted_id
            FROM ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_POSTS . ' ON p.topic_id = t.topic_id AND p.post_id = ( 
                SELECT MAX(post_id)
                FROM ' . TABLE_POSTS . '
                WHERE topic_id = t.topic_id ' . ($deleted === false ? 'AND deleted_id IS NULL' : '' ) . '
            )
            ' . $this->join->user(on: 'CASE WHEN post_created > topic_created THEN p.user_id ELSE t.user_id END', role: true). '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id 
            WHERE ((FIND_IN_SET("' . LOGGED_USER_GROUP_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_GROUP_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see)))' . ($deleted === false ? ' AND t.deleted_id IS NULL' : '' ) . '
            GROUP BY t.topic_id 
            ORDER BY created DESC
            LIMIT ?
        ', [$number], ROWS);

        foreach ($posts as &$_)
        {
            $_['labels'] = [];
            if ($_['count_of_labels'] >= 1)
            {
                $_['labels'] = $this->getLabels($_['topic_id']) ?: [];
            }
        }
        return $posts;
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
     * Returns users who liked post
     *
     * @param  int $ID Post ID
     * @param int $number Number of users
     * 
     * @return array
     */
    public function getLikes( int $ID, int $number = 5 )
    {
        return $this->db->query('
            SELECT u.user_id, u.user_name, u.user_deleted
            FROM ' . TABLE_POSTS_LIKES . '
            LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = pl.user_id
            WHERE post_id = ?
            ORDER BY FIELD(u.user_id, ?) DESC, like_created DESC
            LIMIT ?
        ', [$ID, LOGGED_USER_ID, $number], ROWS);
    }

    /**
     * Returns all users who liked post
     *
     * @param  int $ID Post ID
     * 
     * @return array
     */
    public function likes( int $ID )
    {
        return $this->db->query('
            SELECT ' . $this->select->user(role: true) . ', user_posts, user_reputation, group_name
            FROM ' . TABLE_POSTS_LIKES . '
            ' . $this->join->user(on: 'pl.user_id', role: true) . '
            WHERE pl.post_id = ?
            ORDER BY FIELD(u.user_id, ?) DESC, pl.like_created DESC
        ', [$ID, LOGGED_USER_ID], ROWS);
    }
}