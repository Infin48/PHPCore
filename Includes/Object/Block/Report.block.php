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
 * Report
 */
class Report extends Block
{    
    /**
     * Returns report
     *
     * @param  int $reportID Report ID
     * 
     * @return array
     */
    public function get( int $reportID )
    {
        return $this->db->query('
            SELECT r.*, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS . '
            LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = r.report_type_user_id
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            WHERE report_id = ?
        ', [$reportID]);
    }

    /**
     * Returns last pending reports
     *
     * @param int $number Number of pending reports
     * 
     * @return array
     */
    public function getLastPending( int $number = 5 )
    {
        return $this->db->query('
            SELECT r.*, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS . '
            ' . $this->join->user('r.report_type_user_id'). '
            WHERE report_status = 0
            ORDER BY report_created DESC
            LIMIT ?
        ', [$number], ROWS);
    }

    /**
     * Returns all reported topics
     * 
     * @return array
     */
    public function getAllTopic()
    {
        return $this->db->query('
            SELECT r.*, topic_name, topic_id, topic_url, t.deleted_id, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS . '
            ' . $this->join->user('r.report_type_user_id'). '
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = r.report_type_id
            WHERE r.report_type = "Topic"
            ORDER BY report_status ASC, report_created DESC
            LIMIT ?, ?
        ', [$this->pagination['offset'], $this->pagination['max']], ROWS);
    }
    
    /**
     * Returns all reported posts
     * 
     * @return array
     */
    public function getAllPost()
    {
        return $this->db->query('
            SELECT r.*, p.post_id, t.topic_name, t.topic_id, t.topic_url, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS . '
            ' . $this->join->user('r.report_type_user_id'). '
            LEFT JOIN ' . TABLE_POSTS . ' ON p.post_id = r.report_type_id
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            WHERE report_type = "Post"
            ORDER BY report_status ASC, report_created DESC
            LIMIT ?, ?
        ', [$this->pagination['offset'], $this->pagination['max']], ROWS);
    }
    
    /**
     * Returns users by number of reported content
     *
     * @param int $number Number of users
     * 
     * @return array
     */
    public function getUsers( int $number = 5 )
    {
        return $this->db->query('
            SELECT ' . $this->select->user() . ', ( SELECT COUNT(*) FROM ' . TABLE_REPORTS . '2 WHERE r.report_type_user_id = r2.report_type_user_id) as count
            FROM ' . TABLE_REPORTS . '
            ' . $this->join->user('r.report_type_user_id'). '
            GROUP BY r.report_type_user_id
            ORDER BY count DESC
            LIMIT ?
        ', [$number], ROWS);
    }
    
    /**
     * Returns last solved reports
     *
     * @return array
     */
    public function getLastSolved()
    {
        return $this->db->query('
            SELECT r.*, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS . '
            ' . $this->join->user('r.report_type_user_id'). '
            LEFT JOIN ' . TABLE_REPORTS_REASONS . ' ON rr.report_reason_id = (
                SELECT MAX(report_reason_id)
                FROM ' . TABLE_REPORTS_REASONS . '
                WHERE r.report_id = report_id AND report_reason_type = 1
                ORDER BY report_reason_created DESC
            )
            WHERE report_status = 1
            ORDER BY rr.report_reason_created DESC
            LIMIT 5
        ', [], ROWS);
    }
    
    /**
     * Returns count of report reasons 
     *
     * @param  int $reportID Report ID
     * 
     * @return int
     */
    public function getReasonsCount( int $reportID )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) as count
            FROM ' . TABLE_REPORTS_REASONS . '
            WHERE report_reason_type = 0 AND report_id = ?
        ', [$reportID])['count'];
    }

    /**
     * Returns count of all report reasons 
     *
     * @param  int $reportID Report ID
     * 
     * @return int
     */
    public function getAllReasonsCount( int $reportID )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) as count
            FROM ' . TABLE_REPORTS_REASONS . '
            WHERE report_id = ?
        ', [$reportID])['count'];
    }
    
    /**
     * Returns all report reasons
     *
     * @param  int $reportID Report ID
     * 
     * @return array
     */
    public function getAllReasons( int $reportID )
    {
        return $this->db->query('
            SELECT rr.*, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS_REASONS . '
            ' . $this->join->user('rr.user_id'). '
            WHERE report_id = ?
            ORDER BY report_reason_created ASC
            LIMIT ?, ?
        ', [$reportID, $this->pagination['offset'], $this->pagination['max']], ROWS);
    }
    
    /**
     * Returns all reported profile posts
     *
     * @return array
     */
    public function getAllProfilePost()
    {
        return $this->db->query('
            SELECT r.*, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS . '
            ' . $this->join->user('r.report_type_user_id'). '
            WHERE report_type = "ProfilePost"
            ORDER BY report_status ASC, report_created DESC
            LIMIT ?, ?
        ', [$this->pagination['offset'], $this->pagination['max']], ROWS);
    }
    
    /**
     * Returns all reported profile post comments
     *
     * @return array
     */
    public function getAllProfilePostComment()
    {
        return $this->db->query('
            SELECT r.*, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS . '
            ' . $this->join->user('r.report_type_user_id'). '
            WHERE report_type = "ProfilePostComment"
            ORDER BY report_status ASC, report_created DESC
            LIMIT ?, ?
        ', [$this->pagination['offset'], $this->pagination['max']], ROWS);
    }
    
    /**
     * Returns count of opened reports by content type
     *
     * @return array
     */
    public function getCount()
    {
        return $this->db->query('
            SELECT (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_status = 0
            ) AS total, (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_status = 0 AND report_type = "Post"
            ) AS post, (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_status = 0 AND report_type = "Topic"
            ) AS topic, (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_status = 0 AND report_type = "ProfilePost"
            ) AS profile_post, (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_status = 0 AND report_type = "ProfilePostComment"
            ) AS profile_post_comment
        ');
    }

    /**
     * Returns report statistics
     *
     * @return array
     */
    public function getStats()
    {
        return $this->db->query('
            SELECT (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_type = "Post"
            ) AS post, (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_type = "Topic"
            ) AS topic, (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_type = "ProfilePost"
            ) AS profile_post, (
                SELECT COUNT(*)
                FROM ' . TABLE_REPORTS . '
                WHERE report_type = "ProfilePostComment"
            ) AS profile_post_comment
        ');
    }
}