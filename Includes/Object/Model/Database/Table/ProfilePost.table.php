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
 * ProfilePost
 */
class ProfilePost extends Table
{
    /**
     * Returns profile post
     * 
     * @param  int $ID Profile post ID
     * @param  bool $deleted If true - also deleted profile post will be returned
     * 
     * @return array
     */
    public function get( int $ID, bool $deleted = false )
    {
        return $this->db->query('
            SELECT pp.*, pp.profile_id as profile_user_id, u2.user_name AS profile_user_name, ' . $this->select->user(role: true) . ', r.report_status,
                (SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS . '2 WHERE pp2.profile_post_id >= pp.profile_post_id AND pp2.profile_id = pp.profile_id ' . ($deleted === false ? 'AND pp2.deleted_id IS NULL' : '') . ') AS position,
                CASE WHEN ( SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS_COMMENTS . ' WHERE profile_post_id = pp.profile_post_id ' . ($deleted === false ? 'AND ppc.deleted_id IS NULL' : '') . ') > 5 THEN 1 ELSE 0 END AS next
            FROM ' . TABLE_PROFILE_POSTS . '
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = pp.report_id
            ' . $this->join->user(on: 'pp.user_id', role: true). '
            LEFT JOIN ' . TABLE_USERS . '2 ON u.user_id = pp.profile_id
            WHERE profile_post_id = ? ' . ($deleted === false ? 'AND pp.deleted_id IS NULL' : '') . '
        ', [$ID]);
    }

    /**
     * Returns profile posts from profile
     * 
     * @param int $ID Profile ID
     * @param  bool $deleted If true - also deleted content will be returned
     * 
     * @return array
     */
    public function parent( int $ID, bool $deleted = false )
    {
        return $this->db->query('
            SELECT pp.*, r.report_status, ' . $this->select->user(role: true) . ',
                CASE WHEN ( SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS_COMMENTS . ' WHERE profile_post_id = pp.profile_post_id ' . ($deleted === false ? 'AND ppc.deleted_id IS NULL' : '') . ') > 5 THEN 1 ELSE 0 END AS next
            FROM ' . TABLE_PROFILE_POSTS . '
            ' . $this->join->user(on: 'pp.user_id', role: true). '
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = pp.report_id
            WHERE profile_id = ? ' . ($deleted === false ? 'AND pp.deleted_id IS NULL' : '') . '
            ORDER BY profile_post_created DESC
            LIMIT ?, ?
        ', [$ID, $this->pagination['offset'], $this->pagination['max']], ROWS);

    }

    /**
     * Returns count of profile posts from profile
     *
     * @param  int $ID Profile post ID
     * @param  bool $deleted If true - also deleted content will be counted
     * 
     * @return int
     */
    public function parentCount( int $ID, bool $deleted = false )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_PROFILE_POSTS . '
            WHERE profile_id = ? ' . ($deleted === false ? 'AND pp.deleted_id IS NULL' : '') . '
        ', [$ID])['count'];
    } 

    /**
     * Returns last added profile posts
     *
     * @param  int $number Number of profile posts
     * @param  bool $deleted If true - also deleted content will be returned
     * 
     * @return array
     */
    public function last( int $number = 5, bool $deleted = false )
    {
        return $this->db->query('
            SELECT pp.*, ' . $this->select->user(role: true) . ', u.user_last_activity, pp.profile_id AS profile_user_id, u2.user_name AS profile_user_name,
                g2.group_class AS two_group_class,
                u2.user_name AS two_user_name,
                u2.user_id AS two_user_id,
                u2.user_deleted as two_user_deleted
            FROM ' . TABLE_PROFILE_POSTS . ' 
            ' . $this->join->user(on: 'pp.user_id', role: true) . '
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id = pp.profile_id 
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_id = u2.group_id
            ' . ($deleted === false ? 'WHERE pp.deleted_id IS NULL' : '') . '
            ORDER BY profile_post_created DESC
            LIMIT 0, ?
        ', [$number], ROWS);
    }
}