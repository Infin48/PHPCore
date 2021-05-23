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
 * UserNotification
 */
class UserNotification extends Block
{    
    /**
     * Returns all users notifications from user
     *
     * @param  int $userID User ID
     * 
     * @return array
     */
    public function getParent( int $userID )
    {
        return $this->db->query('
            SELECT un.*, ' . $this->select->user() . '
            FROM ' . TABLE_USERS_NOTIFICATIONS . '
            ' . $this->join->user('un.user_id'). '
            WHERE un.to_user_id = ?
            ORDER BY user_notification_created DESC',
        [$userID], ROWS);
    }
}