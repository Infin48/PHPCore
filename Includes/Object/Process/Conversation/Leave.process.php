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
 * Leave
 */
class Leave extends \Process\ProcessExtend
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
        // DELETE RECIPIENT FROM CONVERSATION
        $this->db->query('
            DELETE cr FROM ' . TABLE_CONVERSATIONS_RECIPIENTS . '
            WHERE conversation_id = ? AND user_id = ?
        ', [$this->data->get('conversation_id'), LOGGED_USER_ID]);

        // REDIRECT USER
        $this->redirectTo('/user/conversation/');
    }
}