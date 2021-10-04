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
            'conversation_name'    => [
                'type' => 'text',
                'required' => true,
                'length_max' => 100
            ],
            'to'                    => [
                'type' => 'array',
                'required' => true,
                'length_max' => 9,
                'block' => '\Block\User.getAllID'
            ],
            'conversation_text'     => [
                'type' => 'html',
                'required' => true,
                'length_max' => 100000
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
        if (in_array(LOGGED_USER_ID, $this->data->get('to'))) {
            return false;
        }

        // INSERT CONVERSATION TO DATABASE
        $this->db->insert(TABLE_CONVERSATIONS, [
            'conversation_url'      => parse($this->data->get('conversation_name')),
            'conversation_text'		=> $this->data->get('conversation_text'),
            'user_id'	            => LOGGED_USER_ID,
            'conversation_name'	=> $this->data->get('conversation_name')
        ]);

        $lastInsertId = $this->db->lastInsertId();

        foreach (array_merge([LOGGED_USER_ID], array_unique($this->data->get('to'))) as $userID) {

            // ADD RECIPIENT
            $this->db->insert(TABLE_CONVERSATIONS_RECIPIENTS, [
                'conversation_id' => $lastInsertId,
                'user_id' => $userID
            ]);

            if ($userID != LOGGED_USER_ID) {
                // UPLOADS USER'S NEW MESSAGE NOTIFICATIONS
                $this->db->insert(TABLE_USERS_UNREAD, [
                    'conversation_id' => $lastInsertId,
                    'user_id' => $userID
                ]);
            }
        }

        $this->redirect('/user/conversation/show/' .$lastInsertId . '.' . parse($this->data->get('conversation_name')));
    }
}