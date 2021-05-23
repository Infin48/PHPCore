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
 * Edit
 */
class Edit extends \Process\ProcessExtend
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
        if (LOGGED_USER_ID != $this->data->get('user_id')) {
            return false;
        }

        // UPDATE PROFILE POST
        $this->db->update(TABLE_PROFILE_POSTS, [
            'profile_post_text' => $this->data->get('text')
        ], $this->data->get('profile_post_id'));

        return true;
    }
}