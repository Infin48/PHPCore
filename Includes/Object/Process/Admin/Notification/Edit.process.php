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
 * Edit
 */
class Edit extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'notification_name' => [
                'type' => 'text',
                'required' => true
            ],
            'notification_text' => [
                'type' => 'text',
                'required' => true
            ],
            'notification_type' => [
                'custom' => [1, 2, 3]
            ],
            'notification_hidden' => [
                'type' => 'checkbox'
            ]
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        // EDIT NOTIFICATION
        $this->db->update(TABLE_NOTIFICATIONS, [
            'notification_name'     => $this->data->get('notification_name'),
            'notification_text'     => $this->data->get('notification_text'),
            'notification_type'     => $this->data->get('notification_type'),
            'notification_hidden'   => $this->data->get('notification_hidden')
        ], $this->data->get('notification_id'));

        // ADD RECORD TO LOG
        $this->log($this->data->get('notification_name'));
    }
}