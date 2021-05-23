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

use Model\Mail\MailVerify;

/**
 * Account
 */
class Account extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'user_name'                 => [
                'type' => 'username',
                'required' => true
            ],
            'user_email'                => [
                'type' => 'email',
                'required' => true
            ],
            'user_password_new'         => [
                'type' => 'password'
            ],
            'user_password_new_confirm' => [
                'type' => 'text'
            ],
            'user_password'             => [
                'type' => 'text',
                'required' => true
            ],
        ],
        'data' => [
            'email_code',
            'current_user_email',
            'current_user_password'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if ($this->db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_name = ? and user_id <> ?', [$this->data->get('user_name'), LOGGED_USER_ID])) {
            throw new \Exception\Notice('user_name_exist');
        }

        if ($this->db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_email = ? and user_id <> ?', [$this->data->get('user_email'), LOGGED_USER_ID])) {
            throw new \Exception\Notice('user_email_exist');
        }

        if ($this->data->get('user_password_new') and !$this->data->get('user_password_new_confirm')) {
            throw new \Exception\Notice('user_password_new_confirm');
        }

        if ($this->check->passwordMatch($this->data->get('user_password'), $this->data->get('current_user_password'), 'user_password_wrong')) {
            
            if ($this->data->get('current_user_email') != $this->data->get('user_email')) {
            
                // GENERATE CODE
                $code = substr(md5(RAND), 0, 15);

                if ($this->data->get('email_code')) {

                    $code = $this->data->get('email_code');

                    $this->db->update(TABLE_VERIFY_EMAIL, [
                        'user_email' => $this->data->get('user_email'),
                    ], LOGGED_USER_ID);
                } else {

                    $this->db->insert(TABLE_VERIFY_EMAIL, [
                        'user_id'       => LOGGED_USER_ID,
                        'user_email'    => $this->data->get('user_email'),
                        'email_code'    => $code
                    ]);
                }

                // SEND MAIL WITH CODE
                $mail = new MailVerify();
                $mail->mail->addAddress($this->data->get('user_email'), $this->data->get('user_name'));
                $mail->assign(['code' => $code]);
                $mail->send();
            }

            if ($this->data->get('user_password_new') and $this->data->get('user_password_new_confirm')) {

                if ($this->check->passwordMatch($this->data->get('user_password_new'), $this->data->get('user_password_new_confirm'))) {
                    
                    // UPDATE USER INFORMATIONS
                    $this->db->update(TABLE_USERS, [
                        'user_password' => password_hash($this->data->get('user_password_new'), PASSWORD_DEFAULT),
                    ], LOGGED_USER_ID);
                }
            }

            // UPDATE USER INFORMATIONS
            $this->db->update(TABLE_USERS, [
                'user_name' 	=> $this->data->get('user_name')
            ], LOGGED_USER_ID);

            return true;

        }
    }
}