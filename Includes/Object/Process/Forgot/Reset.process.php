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

namespace Process\Forgot;

/**
 * Reset
 */
class Reset extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'user_password_new'         => [
                'type' => 'text',
                'required' => true
            ],
            'user_password_new_confirm'  => [
                'type' => 'text',
                'required' => true
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
        'login' => REQUIRE_LOGOUT
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        // IF PASSWORDS MATCH
        if ($this->check->passwordMatch($this->data->get('user_password_new'), $this->data->get('user_password_new_confirm'))) {

            // IF IS PASSWORD VALID
            if ($this->check->password($this->data->get('user_password_new'))) {

                // UPDATE PASSWORD IN DATABASE
                $this->db->update(TABLE_USERS, [
                    'user_password' => password_hash($this->data->get('user_password_new'), PASSWORD_DEFAULT),
                ], $this->data->get('user_id'));

                // DELETE RECORD FROM "FORGOT PASSWORD" TABLE                  
                $this->db->query('DELETE fp FROM ' . TABLE_FORGOT . ' WHERE user_id = ?', [$this->data->get('user_id')]);
    
                return true;
            }
        }
    }
}