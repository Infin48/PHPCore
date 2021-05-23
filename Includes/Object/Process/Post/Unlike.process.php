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

namespace Process\Post;

/**
 * Unlike
 */
class Unlike extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'post_id'
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
            'block' => '\Block\Post',
            'method' => 'get',
            'selector' => 'post_id'
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
            return false;
        }

        if (!in_array(LOGGED_USER_ID, array_column((new \Block\Post)->getLikes($this->data->get('post_id')), 'user_id'))) {
            return false;
        }

        // UNLIKE POST
        $this->db->query('
            DELETE pl FROM ' . TABLE_POSTS_LIKES . '
            WHERE post_id = ? AND user_id = ?
        ', [$this->data->get('post_id'), LOGGED_USER_ID]);

        // REDUCES USER REPUTATION
        $this->db->update(TABLE_USERS, [
            'user_reputation' => [MINUS],
        ],$this->data->get('user_id'));

        // DELETE OLD USER NOTIFICATION
        $this->db->query('
            DELETE un FROM ' . TABLE_USERS_NOTIFICATIONS . '
            WHERE to_user_id = ? AND user_notification_type = "Post/Like" AND user_notification_type_id = ?
        ', [$this->data->get('user_id'), $this->data->get('post_id')]);
    }
}