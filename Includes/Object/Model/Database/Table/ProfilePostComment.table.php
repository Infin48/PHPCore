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
 * ProfilePostComment
 */
class ProfilePostComment extends Table
{
    /**
     * Returns profile post comment
     * 
     * @param  int $ID Profile post comment ID
     * @param  bool $deleted If true - also deleted profile comment will be returned
     * 
     * @return array
     */
    public function get( int $ID, bool $deleted = false )
    {
        return $this->db->query('
            SELECT ppc.*, pp.profile_id, u.user_name, pp.profile_id as profile_user_id, u2.user_name AS profile_user_name, ' . $this->select->user(role: true) . ', r.report_status, pp.deleted_id as deleted_id_profile_post,
                (SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS_COMMENTS . '2 WHERE ppc2.profile_post_comment_id >= ppc.profile_post_comment_id AND ppc2.profile_id = ppc.profile_id ' . ($deleted === false ? 'AND ppc2.deleted_id IS NULL' : '') . ') AS profile_post_comment_position,
                (SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS . '2 WHERE pp2.profile_post_id >= pp.profile_post_id AND pp2.profile_id = pp.profile_id ' . ($deleted === false ? 'AND pp.deleted_id IS NULL' : '') . ') AS profile_post_position
            FROM ' . TABLE_PROFILE_POSTS_COMMENTS . '
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = ppc.report_id
            ' . $this->join->user(on: 'ppc.user_id', role: true). '
            LEFT JOIN ' . TABLE_PROFILE_POSTS . ' ON pp.profile_post_id = ppc.profile_post_id
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id = pp.profile_id
            WHERE profile_post_comment_id = ? ' . ($deleted === false ? 'AND ppc.deleted_id IS NULL' : '') . '
        ', [$ID]);
    }

    /**
     * Returns profile post comments from profile post
     * 
     * @param  int $ID Profile post ID
     * @param  int $limit Number of profile post comments
     * @param  bool $deleted If true - also deleted profile comments will be returned
     * 
     * @return array
     */
    public function parent( int $ID, int|null $limit = 5, bool $deleted = false )
    {
        return array_reverse($this->db->query('
            SELECT ppc.*, r.report_status, ' . $this->select->user(role: true) . '
            FROM ' . TABLE_PROFILE_POSTS_COMMENTS . '
            ' . $this->join->user(on: 'ppc.user_id', role: true). '
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = ppc.report_id
            WHERE profile_post_id = ? ' . ($deleted === false ? 'AND ppc.deleted_id IS NULL' : '') . '
            GROUP BY ppc.profile_post_comment_id
            ORDER BY profile_post_comment_created DESC
            ' . (!is_null($limit) ? 'LIMIT ' . $limit : '' ) . '
        ', [$ID], ROWS));
    }
}