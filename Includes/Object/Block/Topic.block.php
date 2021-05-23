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
 * Topic
 */
class Topic extends Block
{
    /**
     * Returns topic
     *
     * @param  int $topicID Topic ID
     * 
     * @return array
     */
    public function get( int $topicID )
    {
        return $this->db->query('
            SELECT t.*, c.*, f.*, r.report_status, ' . $this->select->user() . ', user_signature, user_topics, user_posts, user_reputation, user_last_activity, group_name, group_index,
                CASE WHEN fpt.forum_id IS NOT NULL THEN 1 ELSE 0 END as topic_permission,
                CASE WHEN fpp.forum_id IS NOT NULL THEN 1 ELSE 0 END as post_permission,
                CASE WHEN ( SELECT COUNT(*) FROM ' . TABLE_TOPICS_LIKES . ' WHERE topic_id = t.topic_id ) > 5 THEN 1 ELSE 0 END AS is_more_likes,
                ( SELECT COUNT(*) FROM ' . TABLE_TOPICS_LIKES . ' WHERE topic_id = t.topic_id ) AS count_of_likes
            FROM ' . TABLE_TOPICS . '
            ' . $this->join->user('t.user_id'). '
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = t.report_id 
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id 
            LEFT JOIN ' . TABLE_CATEGORIES . ' ON c.category_id = f.category_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = t.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_POST . ' ON fpp.forum_id = t.forum_id AND fpp.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_TOPIC . ' ON fpt.forum_id = t.forum_id AND fpt.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = t.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE t.topic_id = ? AND t.deleted_id IS NULL AND fps.forum_id IS NOT NULL AND cps.category_id IS NOT NULL 
        ', [$topicID]);
    }

    /**
     * Returns topic
     * This method is for user notification
     *
     * @param  int $topicID Topic ID
     * 
     * @return array
     */
    public function getUN( int $topicID )
    {
        return $this->db->query('
            SELECT t.topic_id, t.topic_name, t.topic_url, f.forum_id, f.forum_url
            FROM ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id 
            LEFT JOIN ' . TABLE_CATEGORIES . ' ON c.category_id = f.category_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = t.forum_id AND fps.group_id = ' . LOGGED_USER_GROUP_ID . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = t.category_id AND cps.group_id = ' . LOGGED_USER_GROUP_ID . '
            WHERE t.topic_id = ? AND fps.forum_id IS NOT NULL AND cps.category_id IS NOT NULL
        ', [$topicID]);
    }

    /**
     * Returns topics from forum
     *
     * @param  int $forumID Forum ID
     * 
     * @return array
     */
    public function getParent( int $forumID )
    {
        return $this->db->query('
            SELECT t.deleted_id, t.topic_id, topic_name, topic_created, topic_image, ' . $this->select->user() . ', is_sticky, is_locked, topic_url, topic_views, topic_posts,
                p.post_id, u2.user_name AS last_user_name, u2.user_id AS last_user_id, u2.user_profile_image AS last_user_profile_image, p.post_created AS last_post_created, u2.is_deleted as last_is_deleted, g2.group_class_name AS last_group_class_name,
                CASE WHEN tlb.topic_id IS NULL THEN 0 ELSE 1 END AS is_label
            FROM ' . TABLE_TOPICS . ' 
            ' . $this->join->user('t.user_id'). '
            LEFT JOIN ' . TABLE_TOPICS_LABELS . ' ON tlb.topic_id = t.topic_id
            LEFT JOIN ' . TABLE_POSTS . ' ON p.topic_id = t.topic_id AND p.post_id = (
                SELECT MAX(post_id)
                FROM ' . TABLE_POSTS . '
                WHERE topic_id = t.topic_id AND deleted_id IS NULL
            )
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id= p.user_id
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_id= u2.group_id
            WHERE t.forum_id = ? AND t.deleted_id IS NULL
            GROUP BY t.topic_id 
            ORDER BY is_sticky DESC, topic_created DESC 
            LIMIT ?, ?
        ', [$forumID, $this->pagination['offset'], $this->pagination['max']], ROWS);
    }

    /**
     * Returns count of topics from forum
     *
     * @param  int $forumID Forum ID
     * 
     * @return int
     */
    public function getParentCount( int $forumID )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_TOPICS . '
            WHERE forum_id = ? AND deleted_id IS NULL
        ', [$forumID])['count'];
    }
    
    /**
     * Returns users who liked topic
     *
     * @param  int $topicID Topic ID
     * @param  int $number Number of users
     * 
     * @return array
     */
    public function getLikes( int $topicID, int $number = 5 )
    {
        return $this->db->query('
            SELECT u.user_id, user_name, u.is_deleted
            FROM ' . TABLE_TOPICS_LIKES . '
            LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = tl.user_id
            WHERE tl.topic_id = ?
            ORDER BY FIELD(u.user_id, ?) DESC, like_created DESC
            LIMIT ?
        ', [$topicID, LOGGED_USER_ID, $number], ROWS);
    }

    /**
     * Returns all users who liked topic
     *
     * @param  int $topicID Topic ID
     * 
     * @return array
     */
    public function getLikesAll( int $topicID )
    {
        return $this->db->query('
            SELECT ' . $this->select->user() . ', user_posts, user_reputation
            FROM ' . TABLE_TOPICS_LIKES . '
            ' . $this->join->user('tl.user_id'). '
            WHERE tl.topic_id = ?
            ORDER BY FIELD(u.user_id, ?) DESC, tl.like_created DESC
        ', [$topicID, LOGGED_USER_ID], ROWS);
    }
    
    /**
     * Returns labels from topic
     *
     * @param  int $topicID Topic ID
     * 
     * @return array
     */
    public function getLabels( int $topicID )
    {
        return $this->db->query('
            SELECT label_name, label_class_name, l.label_id
            FROM ' . TABLE_TOPICS_LABELS . '
            LEFT JOIN ' . TABLE_LABELS . ' ON l.label_id = tlb.label_id
            WHERE tlb.topic_id = ?
            ORDER BY l.position_index DESC
        ', [$topicID], ROWS);
    }
}