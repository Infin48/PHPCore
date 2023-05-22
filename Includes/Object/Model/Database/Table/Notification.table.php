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
 * Notification
 */
class Notification extends Table
{    
    /**
     * Returns notification
     *
     * @param  int $ID Notification ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_NOTIFICATIONS . ' WHERE notification_id = ?', [$ID]);
    }

    /**
     * Returns ID of all notifications
     *
     * @return array
     */
    public function all()
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_NOTIFICATIONS . '
            ORDER BY position_index DESC
        ', [], ROWS);
    }
}