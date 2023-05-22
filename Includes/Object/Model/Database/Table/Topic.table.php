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
 * Topic
 */
class Topic extends Table
{
    /**
     * Returns all topics with given label
     *
     * @param  int $labelID Label ID
     * @param  bool $deleted If true - deleted topic will be returned
     * 
     * @return array
     */
    public function label( int $labelID, bool $deleted = false )
    {
        $topics = $this->db->query('
            SELECT t.deleted_id, t.topic_id, topic_name, topic_created, ' . $this->select->user(role: true) . ', topic_image, topic_attachments, topic_sticked, topic_locked, topic_url, topic_views, topic_posts,
                p.post_id, u2.user_name AS last_user_name, u2.user_id AS last_user_id, u2.user_profile_image AS last_user_profile_image, p.post_created AS last_post_created, u2.user_deleted as last_user_deleted, g2.group_class AS last_group_class,
                ro2.role_name as last_role_name, ro2.role_class AS last_role_class, ro2.role_color AS last_role_color, ro2.role_icon AS last_role_icon,
                CASE WHEN p.post_created IS NULL THEN t.topic_created ELSE p.post_created END AS created,
                ( SELECT COUNT(*) FROM ' . TABLE_TOPICS_LABELS . ' WHERE tlb.topic_id = t.topic_id ) AS count_of_labels

            FROM ' . TABLE_TOPICS . ' 
            ' . $this->join->user(on: 't.user_id', role: true). '
            JOIN ' . TABLE_TOPICS_LABELS . ' ON tlb.topic_id = t.topic_id AND tlb.label_id = ?
            LEFT JOIN ' . TABLE_POSTS . ' ON p.topic_id = t.topic_id AND p.post_id = (
                SELECT MAX(post_id)
                FROM ' . TABLE_POSTS . '
                WHERE topic_id = t.topic_id ' . ($deleted === false ? 'AND deleted_id IS NULL' : '') . '
            )
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id= p.user_id
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_id= u2.group_id
            LEFT JOIN ' . TABLE_ROLES . '2 ON ro2.role_id = (
                SELECT role_id
                FROM ' . TABLE_ROLES . 'l2
                WHERE FIND_IN_SET(rol2.role_id, u2.user_roles)
                ORDER BY rol2.position_index DESC
                LIMIT 1
            )
            ' . ($deleted === false ? 'WHERE t.deleted_id IS NULL' : '') . '
            GROUP BY t.topic_id 
            ORDER BY created DESC 
            LIMIT ?, ?
        ', [$labelID, $this->pagination['offset'], $this->pagination['max']], ROWS);

            
        foreach ($topics as &$_)
        {
            if ($_['count_of_labels'] >= 1)
            {
                $_['labels'] = $this->getLabels($_['topic_id']) ?: [];
            }
        }
        return $topics;
    }

    /**
     * Returns count of topics with given label
     *
     * @param  int $labelID Label ID
     * @param  bool $deleted If true - deleted topic will be counted
     * 
     * @return int
     */
    public function labelCount( int $labelID, bool $deleted = false )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) as count
            FROM ' . TABLE_TOPICS . '
            JOIN ' . TABLE_TOPICS_LABELS . ' ON tlb.topic_id = t.topic_id AND tlb.label_id = ?
            ' . ($deleted === false ? 'WHERE t.deleted_id IS NULL' : '') . '
        ', [$labelID])['count'];
    }

    /**
     * Returns topic
     *
     * @param  int $ID Topic ID
     * @param  bool $deleted If true - also deleted topic will be returned
     * 
     * @return array
     */
    public function get( int $ID, bool $deleted = false )
    {
        $topic = $this->db->query('
            SELECT t.*, c.*, f.*, re.report_status, ' . $this->select->user(role: true) . ', user_topics, user_posts, user_reputation, group_name, group_index, cp.*, fp.*, fp.permission_see as permission_see_forum, cp.permission_see as permission_see_category,
                ( SELECT COUNT(*) FROM ' . TABLE_TOPICS_LIKES . ' WHERE topic_id = t.topic_id ) AS count_of_likes,
                ( SELECT COUNT(*) FROM ' . TABLE_TOPICS_LABELS . ' WHERE topic_id = t.topic_id ) AS count_of_labels
            FROM ' . TABLE_TOPICS . '
            ' . $this->join->user(on: 't.user_id', role: true). '
            LEFT JOIN ' . TABLE_REPORTS . 'e ON re.report_id = t.report_id 
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id 
            LEFT JOIN ' . TABLE_CATEGORIES . ' ON c.category_id = f.category_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id
            WHERE t.topic_id = ? ' . ($deleted === false ? 'AND t.deleted_id IS NULL' : '') . '
        ', [$ID]);

        if (!$topic)
        {
            return [];
        }

        $topic['permission_see_forum'] = explode(',', $topic['permission_see_forum']);
        $topic['permission_see_category'] = explode(',', $topic['permission_see_category']);

        if (in_array('*', $topic['permission_see_forum']))
        {
            $topic['permission_see_forum'] = $topic['permission_see_category'];
        }

        if (in_array('*', $topic['permission_see_category']))
        {
            $topic['permission_see_category'] = $topic['permission_see_forum'];
        }

        $topic['permission_see'] = array_intersect($topic['permission_see_category'], $topic['permission_see_forum']);
        $topic['permission_post'] = explode(',', $topic['permission_post']);
        $topic['permission_topic'] = explode(',', $topic['permission_topic']);

        $topic['likes'] = [];
        if ($topic['count_of_likes'] >= 1)
        {
            $topic['likes'] = $this->getLikes($ID);
        }

        $topic['labels'] = [];
        if ($topic['count_of_labels'] >= 1)
        {
            $topic['labels'] = $this->getLabels($ID);
        }

        return $topic;
    }

    /**
     * Returns topics from forum
     *
     * @param  int $ID Forum ID
     * @param  bool $deleted If true - also deleted topics will be returned
     * 
     * @return array
     */
    public function parent( int $ID, bool $deleted = false )
    {
        $topics = $this->db->query('
            SELECT t.deleted_id, t.topic_id, topic_name, topic_created, ' . $this->select->user(role: true) . ', topic_image, topic_attachments, topic_sticked, topic_locked, topic_url, topic_views, topic_posts,
                p.post_id, u2.user_name AS last_user_name, u2.user_id AS last_user_id, u2.user_profile_image AS last_user_profile_image, p.post_created AS last_post_created, u2.user_deleted as last_user_deleted, g2.group_class AS last_group_class,
                ro2.role_name as last_role_name, ro2.role_class AS last_role_class, ro2.role_color AS last_role_color, ro2.role_icon AS last_role_icon,
                CASE WHEN p.post_created IS NULL THEN t.topic_created ELSE p.post_created END AS created,
                ( SELECT COUNT(*) FROM ' . TABLE_TOPICS_LABELS . ' WHERE tlb.topic_id = t.topic_id ) AS count_of_labels

            FROM ' . TABLE_TOPICS . ' 
            ' . $this->join->user(on: 't.user_id', role: true). '
            LEFT JOIN ' . TABLE_POSTS . ' ON p.topic_id = t.topic_id AND p.post_id = (
                SELECT MAX(post_id)
                FROM ' . TABLE_POSTS . '
                WHERE topic_id = t.topic_id ' . ($deleted === false ? 'AND deleted_id IS NULL' : '') . '
            )
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id= p.user_id
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_id= u2.group_id
            LEFT JOIN ' . TABLE_ROLES . '2 ON ro2.role_id = (
                SELECT role_id
                FROM ' . TABLE_ROLES . 'l2
                WHERE FIND_IN_SET(rol2.role_id, u2.user_roles)
                ORDER BY rol2.position_index DESC
                LIMIT 1
            )
            WHERE t.forum_id = ? ' . ($deleted === false ? 'AND t.deleted_id IS NULL' : '') . '
            GROUP BY t.topic_id 
            ORDER BY topic_sticked DESC, created DESC 
            LIMIT ?, ?
        ', [$ID, $this->pagination['offset'], $this->pagination['max']], ROWS);

            
        foreach ($topics as &$_)
        {
            if ($_['count_of_labels'] >= 1)
            {
                $_['labels'] = $this->getLabels($_['topic_id']) ?: [];
            }
        }
        return $topics;
    }

    /**
     * Returns count of topics from forum
     *
     * @param  int $ID Forum ID
     * @param  bool $deleted If true deleted topic will be counted
     * 
     * @return int
     */
    public function parentCount( int $ID, bool $deleted = false )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_TOPICS . '
            WHERE forum_id = ? ' . ($deleted === false ? 'AND t.deleted_id IS NULL' : '') . '
        ', [$ID])['count'];
    }
    
    /**
     * Returns users who liked topic
     *
     * @param  int $ID Topic ID
     * @param  int $number Number of users
     * 
     * @return array
     */
    public function getLikes( int $ID, int $number = 5 )
    {
        return $this->db->query('
            SELECT u.user_id, user_name, u.user_deleted
            FROM ' . TABLE_TOPICS_LIKES . '
            LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = tl.user_id
            WHERE tl.topic_id = ?
            ORDER BY FIELD(u.user_id, ?) DESC, like_created DESC
            LIMIT ?
        ', [$ID, LOGGED_USER_ID, $number], ROWS);
    }

    /**
     * Returns all users who liked topic
     *
     * @param  int $ID Topic ID
     * 
     * @return array
     */
    public function likes( int $ID )
    {
        return $this->db->query('
            SELECT ' . $this->select->user(role: true) . ', user_posts, user_reputation, group_name
            FROM ' . TABLE_TOPICS_LIKES . '
            ' . $this->join->user(on: 'tl.user_id', role: true). '
            WHERE tl.topic_id = ?
            ORDER BY FIELD(u.user_id, ?) DESC, tl.like_created DESC
        ', [$ID, LOGGED_USER_ID], ROWS);
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
}