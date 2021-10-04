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

namespace Process\ProfilePostComment;

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

            // PROFILE COMMENT TEXT
            'text' => [
                'type' => 'html',
                'required' => true,
                'length_max' => 5000
            ]
        ],
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
     * @var string $HTML HTML configuration
     */
    public string $HTML = 'small';

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        // CREATES NEW PROFILE SUB POST
        $this->db->insert(TABLE_PROFILE_POSTS_COMMENTS, [
            'user_id'               => LOGGED_USER_ID,
            'profile_post_id'       => $this->data->get('profile_post_id'),
            'profile_post_comment_text' => $this->data->get('text')
        ]);

        self::$id = $this->db->lastInsertId();

        // SEND USER NOTIFICATION
        $this->notifi(
            id: $this->db->lastInsertId(),
            to: $this->data->get('user_id')
        );

        return true;
    }
}