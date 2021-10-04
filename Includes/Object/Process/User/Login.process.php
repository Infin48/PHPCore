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

namespace Process\User;

use Model\Account\Login as ModelLogin;

/**
 * Login
 */
class Login extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'user_name'     => [
                'type' => 'text',
                'required' => true
            ],
            'user_password' => [
                'type' => 'text',
                'required' => true
            ],
            'remember'      => [
                'type' => 'checkbox'
            ]
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'login' => REQUIRE_LOGOUT
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $login = new ModelLogin($this->data->get('user_name'), $this->data->get('user_password'), (int)$this->data->get('remember'));

        if ($login->login() === true) {
            session_regenerate_id(true);
        }

        $this->redirect(INDEX);
    }
}