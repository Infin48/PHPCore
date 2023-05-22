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
 * ReportReason
 */
class ReportReason extends Table
{    
    /**
     * Returns count of report reasons 
     *
     * @param  int $reportID Report ID
     * 
     * @return int
     */
    public function count( int $reportID )
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
    public function countWithLog( int $reportID )
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
    public function all( int $reportID )
    {
        return $this->db->query('
            SELECT rr.*, ' . $this->select->user() . '
            FROM ' . TABLE_REPORTS_REASONS . '
            ' . $this->join->user('rr.user_id'). '
            WHERE report_id = ?
            ORDER BY report_reason_created DESC
            LIMIT ?, ?
        ', [$reportID, $this->pagination['offset'], $this->pagination['max']], ROWS);
    }
}