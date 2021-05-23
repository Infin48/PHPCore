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
 * ProfilePost
 */
class ProfilePost extends Block
{
    /**
     * Returns profile post
     * 
     * @param int $profilePostID Profile post ID
     * 
     * @return array
     */
    public function get( int $profilePostID )
    {
        return $this->db->query('
            SELECT pp.*, u.user_id AS profile_user_id, u.user_name AS profile_user_name,
                (SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS . '2 WHERE pp2.profile_post_id >= pp.profile_post_id AND pp2.profile_id = pp.profile_id AND pp.deleted_id IS NULL) AS position
            FROM ' . TABLE_PROFILE_POSTS . '
            LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = pp.profile_id
            WHERE profile_post_id = ? AND pp.deleted_id IS NULL
        ', [$profilePostID]);
    }

    /**
     * Returns profile post
     * This method is for user notification
     *
     * @param  int $profilePostID Profile post ID
     * 
     * @return array
     */
    public function getUN( int $profilePostID )
    {
        return $this->db->query('
            SELECT profile_post_id, u.user_id, u.user_name, (SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS . '2 WHERE pp2.profile_post_id >= pp.profile_post_id AND pp2.profile_id = pp.profile_id AND pp.deleted_id IS NULL) AS position
            FROM ' . TABLE_PROFILE_POSTS . '
            LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = pp.profile_id
            WHERE profile_post_id = ?
        ', [$profilePostID]);
    }

    /**
     * Returns profile posts from profile
     * 
     * @param int $profileID Profile ID
     * 
     * @return array
     */
    public function getParent( int $profileID )
    {
        return $this->db->query('
            SELECT pp.*, r.report_status, ' . $this->select->user() . ', user_last_activity,
                CASE WHEN ( SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS_COMMENTS . ' WHERE profile_post_id = pp.profile_post_id AND ppc.deleted_id IS NULL) > 5 THEN 1 ELSE 0 END AS next
            FROM ' . TABLE_PROFILE_POSTS . '
            ' . $this->join->user('pp.user_id'). '
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = pp.report_id
            WHERE profile_id = ? AND pp.deleted_id IS NULL
            ORDER BY profile_post_created DESC
            LIMIT ?, ?
        ', [$profileID, $this->pagination['offset'], $this->pagination['max']], ROWS);

    }

    /**
     * Returns count of profile posts from profile
     *
     * @param  int $profileID Profile post ID
     * 
     * @return int
     */
    public function getParentCount( int $profileID )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_PROFILE_POSTS . '
            WHERE profile_id = ? AND pp.deleted_id IS NULL
        ', [$profileID])['count'];
    } 

    /**
     * Returns last added profile posts
     *
     * @param int $number Number of profile posts
     * 
     * @return array
     */
    public function getLast( int $number = 5 )
    {
        return $this->db->query('
            SELECT profile_id, profile_post_text, profile_post_created, profile_post_id, ' . $this->select->user() . ', u.user_last_activity,
                g2.group_class_name AS two_group_class_name,
                u2.user_name AS two_user_name,
                u2.user_id AS two_user_id,
                u2.is_deleted as two_is_deleted
            FROM ' . TABLE_PROFILE_POSTS . ' 
            ' . $this->join->user('pp.user_id'). '
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id = pp.profile_id 
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_id = u2.group_id
            WHERE pp.deleted_id IS NULL
            ORDER BY profile_post_created DESC
            LIMIT 0, ?
        ', [$number], ROWS);
    }
}