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
 * Log
 */
class Log extends Block
{    
    /**
     * Returns all records from log
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_LOG . '
            ' . $this->join->user('lg.user_id'). '
            ORDER BY log_id DESC
            LIMIT ?, ?
        ',[$this->pagination['offset'], $this->pagination['max']], ROWS);
    }

    /**
     * Returns count of records from log
     * 
     * @return int
     */
    public function getAllCount()
    {
        return (int)$this->db->query('SELECT COUNT(*) as count FROM ' . TABLE_LOG)['count'];
    }

    /**
     * Returns last records from audit log
     *
     * @param int $number Number of records
     * 
     * @return array
     */
    public function getLast( int $number = 5 )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_LOG . '
            ' . $this->join->user('lg.user_id'). '
            ORDER BY log_id DESC
            LIMIT ?
        ',[$number], ROWS);
    }
}