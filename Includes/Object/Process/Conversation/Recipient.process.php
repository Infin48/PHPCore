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

namespace Process\Conversation;

use Block\Conversation as BlockConversation;

/**
 * Recipient
 */
class Recipient extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'user_name' => [
                'type' => 'text',
                'required' => true
            ]
        ],
        'data' => [
            'conversation_id'
        ],
        'block' => [
            'user_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\User',
            'method' => 'getByName',
            'selector' => 'user_name'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if (LOGGED_USER_ID == $this->data->get('user_id')) {
            throw new \Exception\Notice('conversation_user_myself');
        }

        $conversation = new BlockConversation();
        $recipients = array_column($conversation->getRecipients($this->data->get('conversation_id')), 'user_id');

        if (in_array($this->data->get('user_id'), $recipients)) {
            throw new \Exception\Notice('conversation_user_exist');
        }

        if (count($recipients) >= 10) {
            return false;
        }

        $this->db->insert(TABLE_CONVERSATIONS_RECIPIENTS, [
            'conversation_id' => $this->data->get('conversation_id'),
            'user_id' => $this->data->get('user_id')
        ]);

        $this->db->insert(TABLE_USERS_UNREAD, [
            'conversation_id' => $this->data->get('conversation_id'),
            'user_id' => $this->data->get('user_id')
        ]);
    }
}