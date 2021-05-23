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
 * Create
 */
class Create extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [

            // PROFILE POST TEXT
            'text' => [
                'type' => 'html',
                'required' => true,
                'length_max' => 5000,
            ]
        ],
        'data' => [
            'user_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\User',
            'method' => 'get',
            'selector' => 'user_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->insert(TABLE_PROFILE_POSTS, [
            'user_id'           => LOGGED_USER_ID,
            'profile_id'        => $this->data->get('user_id'),
            'profile_post_text' => $this->data->get('text')
        ]);

        $this->id = $this->db->lastInsertId();

        // SEND USER NOTIFICATION
        $this->notifi(
            id: $this->db->lastInsertId(),
            to: $this->data->get('user_id')
        );

        return true;
    }
}