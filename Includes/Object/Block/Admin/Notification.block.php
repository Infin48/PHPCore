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

namespace Block\Admin;

/**
 * Notification
 */
class Notification extends \Block\Notification
{    
    /**
     * Returns all notifications
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_NOTIFICATIONS . ' ORDER BY position_index DESC', [], ROWS);
    }
}