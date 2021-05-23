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

namespace Process\ConversationMessage;

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
            
            // MESSAGE TEXT
            'text'  => [
                'type' => 'html',
                'required' => true,
                'length_max' => 10000,
            ]
        ],
        'data' => [
            'conversation_message_id'
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
            'block' => '\Block\ConversationMessage',
            'method' => 'get',
            'selector' => 'conversation_message_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if (LOGGED_USER_ID != $this->data->get('user_id')) {
            return false;
        }

        // EDIT MESSAGE
        $this->db->update(TABLE_CONVERSATIONS_MESSAGES, [
            'is_edited' => '1',
            'conversation_message_text'	=> $this->data->get('text'),
            'conversation_message_edited' => DATE_DATABASE
        ], $this->data->get('conversation_message_id'));

        return true;
    }
}