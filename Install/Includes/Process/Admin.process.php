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

use Model\JSON;

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
        $this->db->query('TRUNCATE `phpcore_users`');
        $this->db->query('INSERT INTO `phpcore_users` (`user_id`, `user_name`, `user_email`, `user_password`, `user_profile_image`, `group_id`, `user_admin`, `user_topics`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
            '1',
            $this->data->get('user_name'),
            $this->data->get('user_email'),
            password_hash($this->data->get('user_password'), PASSWORD_DEFAULT),
            getProfileImageColor(),
            '2',
            '1',
            '1'
        ]);

        $JSON = new JSON('/Install/Includes/Settings.json');
        $JSON->set('db', true);
        $JSON->set('page', 'install-site');
        $JSON->set('back', false);
        $JSON->save();
    }
}