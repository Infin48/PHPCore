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

namespace Process\Topic;

/**
 * Stick
 */
class Stick extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'topic_id'
        ],
        'block' => [
            'user_id',
            'topic_name'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Topic',
            'method' => 'get',
            'selector' => 'topic_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        // STICK TOPIC
        $this->db->update(TABLE_TOPICS, [
            'is_sticky' => '1'
        ], $this->data->get('topic_id'));

        // SEND NOTIFICATION
        $this->notifi(
            id: $this->data->get('topic_id'),
            to: $this->data->get('user_id'),
            replace: true
        );

        // ADD RECORD TO LOG
        $this->log($this->data->get('topic_name'));
    }
}