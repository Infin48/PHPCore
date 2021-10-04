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
 * Mark
 */
class Mark extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'conversation_id'
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
        $this->db->insert(TABLE_USERS_UNREAD, [
            'conversation_id' => $this->data->get('conversation_id'),
            'user_id' => LOGGED_USER_ID
        ]);
        $this->redirect('/user/conversation/');
    }
}