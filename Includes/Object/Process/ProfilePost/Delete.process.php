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

namespace Process\ProfilePost;

/**
 * Delete
 */
class Delete extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'profile_post_id'
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
            'block' => '\Block\ProfilePost',
            'method' => 'get',
            'selector' => 'profile_post_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->insert(TABLE_DELETED_CONTENT, [
            'user_id' => LOGGED_USER_ID,
            'deleted_type' => 'ProfilePost',
            'deleted_type_id' => $this->data->get('profile_post_id'),
            'deleted_type_user_id' => $this->data->get('user_id')
        ]);

        self::$id = $this->db->lastInsertID();

        $this->db->query('
            UPDATE ' . TABLE_PROFILE_POSTS . ' SET deleted_id = ? WHERE profile_post_id = ?
        ', [self::$id, $this->data->get('profile_post_id')]);

        // SEND USER NOTIFICATION
        $this->notifi(
            id: $this->data->get('profile_post_id'),
            to: $this->data->get('user_id')
        );

        // ADD RECORD TO LOG
        $this->log();
    }
}