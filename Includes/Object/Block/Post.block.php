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
 * Post
 */
class Post extends Block
{
    /**
     * Returns post 
     *
     * @param  int $postID Post ID
     * 
     * @return array
     */
    public function get( int $postID )
    {
        return $this->db->query('
            SELECT p.*, t.topic_id, t.is_locked, t.topic_url, t.topic_name, t.topic_posts, CASE WHEN fpp.forum_id IS NOT NULL THEN 1 ELSE 0 END AS post_permission,
                (SELECT COUNT(*) FROM ' . TABLE_POSTS . '2 WHERE p2.post_id <= p.post_id AND p2.topic_id = p.topic_id AND p2.deleted_id IS NULL) AS position
            FROM ' . TABLE_POSTS . ' 
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = t.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = t.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_POST . ' ON fpp.forum_id = t.forum_id AND fpp.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE p.post_id = ? AND p.deleted_id IS NULL AND t.deleted_id IS NULL AND cps.category_id IS NOT NULL AND fps.forum_id IS NOT NULL
        ', [$postID]);
    }

    /**
     * Returns post
     * This method is for user notification
     *
     * @param  int $postID Post ID
     * 
     * @return array
     */
    public function getUN( int $postID )
    {
        return $this->db->query('
            SELECT p.post_id, t.topic_id, t.topic_url, t.topic_name, (SELECT COUNT(*) FROM ' . TABLE_POSTS . '2 WHERE p2.post_id <= p.post_id AND p2.topic_id = p.topic_id AND p2.deleted_id IS NULL) AS position
            FROM ' . TABLE_POSTS . ' 
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = t.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = t.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE p.post_id = ? AND cps.category_id IS NOT NULL AND fps.forum_id IS NOT NULL
        ', [$postID]);
    }
    
    /**
     * Returns posts from topic
     *
     * @param  int $topicID Topic ID
     * 
     * @return array
     */
    public function getParent( int $topicID )
    {
        return $this->db->query('
            SELECT p.*, t.topic_name, ' . $this->select->user() . ', user_last_activity, user_signature, user_posts, user_topics, group_name, user_reputation,
                CASE WHEN pl.post_id IS NULL THEN 0 ELSE 1 END AS is_like, t.topic_name,
                CASE WHEN ( SELECT COUNT(*) FROM ' . TABLE_POSTS_LIKES . ' WHERE post_id = p.post_id ) > 5 THEN 1 ELSE 0 END AS is_more_likes,
                ( SELECT COUNT(*) FROM ' . TABLE_POSTS_LIKES . ' WHERE post_id = p.post_id ) AS count_of_likes
            FROM ' . TABLE_POSTS . '
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            ' . $this->join->user('p.user_id'). '
            LEFT JOIN ' . TABLE_POSTS_LIKES . ' ON pl.post_id = p.post_id
            WHERE p.topic_id = ? AND p.deleted_id IS NULL
            GROUP BY p.post_id 
            ORDER BY post_created ASC 
            LIMIT ?, ?
        ', [$topicID, $this->pagination['offset'], $this->pagination['max']], ROWS);
    }

    /**
     * Returns number of posts in topic
     *
     * @param  int $topicID Topic ID
     * 
     * @return int
     */
    public function getParentCount( int $topicID )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_POSTS . '
            WHERE topic_id = ? AND deleted_id IS NULL
        ', [$topicID])['count'];
    }

    /**
     * Returns last added posts
     * 
     * @param int $number Number of posts
     *
     * @return array
     */
    public function getlast( int $number = 5 )
    {
        return $this->db->query('
            SELECT t.topic_id, topic_name, is_locked, is_sticky, topic_url, t.forum_id, post_id, ' . $this->select->user() . ', user_last_activity, CASE WHEN post_created > topic_created THEN post_created ELSE topic_created END AS created
            FROM ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_POSTS . ' ON p.topic_id = t.topic_id AND p.post_id = ( 
                SELECT MAX(post_id)
                FROM ' . TABLE_POSTS . '
                WHERE topic_id = t.topic_id AND deleted_id IS NULL
            )
            ' . $this->join->user('CASE WHEN post_created > topic_created THEN p.user_id ELSE t.user_id END'). '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id 
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = t.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = t.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE t.deleted_id IS NULL AND p.deleted_id IS NULL AND fps.forum_id IS NOT NULL AND cps.category_id IS NOT NULL
            GROUP BY t.topic_id
            ORDER BY created DESC
            LIMIT ?
        ', [$number], ROWS);
    }
    
    /**
     * Returns users who liked post
     *
     * @param  int $postID Post ID
     * @param int $number Number of users
     * 
     * @return array
     */
    public function getLikes( int $postID, int $number = 5 )
    {
        return $this->db->query('
            SELECT u.user_id, u.user_name, u.is_deleted
            FROM ' . TABLE_POSTS_LIKES . '
            LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = pl.user_id
            WHERE post_id = ?
            ORDER BY FIELD(u.user_id, ?) DESC, like_created DESC
            LIMIT ?
        ', [$postID, LOGGED_USER_ID, $number], ROWS);
    }

    /**
     * Returns all users who liked post
     *
     * @param  int $postID Post ID
     * 
     * @return array
     */
    public function getLikesAll( int $postID )
    {
        return $this->db->query('
            SELECT ' . $this->select->user() . ', user_posts, user_reputation
            FROM ' . TABLE_POSTS_LIKES . '
            ' . $this->join->user('pl.user_id'). '
            WHERE pl.post_id = ?
            ORDER BY FIELD(u.user_id, ?) DESC, pl.like_created DESC
        ', [$postID, LOGGED_USER_ID], ROWS);
    }
}