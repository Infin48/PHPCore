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
 * Edit
 */
class Edit extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [

            // POST TEXT
            'text' => [
                'type' => 'html',
                'required' => true,
                'length_max' => 10000,
            ],
        ],
        'data' => [
            'post_id'
        ],
        'block' => [
            'user_id',
            'topic_locked',
            'post_permission'
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
        // IF POST IS NOT FROM LOGGED USER
        if (LOGGED_USER_ID != $this->data->get('user_id')) {
            return false;
        }

        // IF USER DOESN'T HAVE PERMISSION TO EDIT POST
        if ($this->data->get('post_permission') != 1) {
            return false;
        }

        // IF TOPIC IS LOCKED
        if ($this->data->get('topic_locked') == 1) {
            return false;
        }

        // EDITS POST
        $this->db->update(TABLE_POSTS, [
            'post_text'         => $this->data->get('text'),
            'post_edited'       => '1',
            'post_edited_at'    => DATE_DATABASE
        ], $this->data->get('post_id'));
    }
}