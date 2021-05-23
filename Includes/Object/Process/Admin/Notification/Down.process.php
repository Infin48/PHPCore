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

namespace Process\Admin\Notification;

/**
 * Down
 */
class Down extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'notification_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Notification',
            'method' => 'get',
            'selector' => 'notification_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->query('
            UPDATE ' . TABLE_NOTIFICATIONS . '
            LEFT JOIN ' . TABLE_NOTIFICATIONS . '2 ON n2.position_index = n.position_index - 1
            SET n.position_index = n.position_index - 1,
                n2.position_index = n2.position_index + 1
            WHERE n.notification_id = ? AND n2.notification_id IS NOT NULL
        ', [$this->data->get('notification_id')]);

        // ADD RECORD TO LOG
        $this->log();
    }
}