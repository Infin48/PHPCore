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

namespace Process;

/**
 * Admin
 */
class Admin extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'user_name'      => [
                'type' => 'username',
                'required' => true
            ],
            'user_email'  => [
                'type' => 'email',
                'required' => true
            ],
            'user_password'  => [
                'type' => 'password',
                'required' => true
            ]
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->query('INSERT INTO `phpcore_users` (`user_name`, `user_email`, `user_password`, `user_profile_image`, `group_id`, `is_admin`, `user_topics`) VALUES (?, ?, ?, ?, ?, ?, ?)', [
            $this->data->get('user_name'),
            $this->data->get('user_email'),
            password_hash($this->data->get('user_password'), PASSWORD_DEFAULT),
            getProfileImageColor(),
            '2',
            '1',
            '1'
        ]);

        $this->system->install([
            'db' => true,
            'page' => 5,
        ]);
    }
}