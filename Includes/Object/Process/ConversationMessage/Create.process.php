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

use Block\Conversation;

/**
 * Create
 */
class Create extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [

            // CONVERSATION MESSAGE TEXT
            'text'  => [
                'type' => 'html',
                'required' => true,
                'length_max' => 10000,
            ]
        ],
        'data' => [
            'conversation_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Conversation',
            'method' => 'get',
            'selector' => 'conversation_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        // INSERT MESSAGE
        $this->db->insert(TABLE_CONVERSATIONS_MESSAGES, [
            'conversation_id'			            => $this->data->get('conversation_id'),
            'user_id' 		            => LOGGED_USER_ID,
            'conversation_message_text'	=> $this->data->get('text')
        ]);

        self::$id = $this->db->lastInsertId();

        // EDIT PRIVATE MESSAGE
        $this->db->update(TABLE_CONVERSATIONS, [
            'conversation_messages' => [PLUS],
        ], $this->data->get('conversation_id'));

        // GET UNREAD USERS
        $unread = array_column($this->db->query('
            SELECT user_id
            FROM ' . TABLE_USERS_UNREAD . '
            WHERE conversation_id = ?
        ', [$this->data->get('conversation_id')], ROWS), 'user_id');

        $conversation = new Conversation();

        // SET UNREAD TO RECIPIENTS
        foreach (array_column($conversation->getRecipients($this->data->get('conversation_id')), 'user_id') as $userID) {

            if ($userID != LOGGED_USER_ID) {

                if (in_array($userID, $unread) === false) {
                    // UPLOADS USER'S NEW MESSAGE NOTIFICATIONS
                    $this->db->insert(TABLE_USERS_UNREAD, [
                        'conversation_id' => $this->data->get('conversation_id'),
                        'user_id' => $userID
                    ]);
                }
            }
        }
        
        return true;
    }
}