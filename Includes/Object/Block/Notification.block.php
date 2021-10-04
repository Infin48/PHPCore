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
 * Notification
 */
class Notification extends Block
{    
    /**
     * Returns notification
     *
     * @param  int $notificationID Notification ID
     * 
     * @return array
     */
    public function get( int $notificationID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_NOTIFICATIONS . ' WHERE notification_id = ?', [$notificationID]);
    }

    /**
     * Returns ID of all notifications
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_NOTIFICATIONS . ' WHERE notification_hidden = 0 ORDER BY position_index DESC', [], ROWS);
    }
}