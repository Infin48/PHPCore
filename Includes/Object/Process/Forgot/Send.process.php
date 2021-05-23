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

use Model\Mail\MailForgot;

/**
 * Send
 */
class Send extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'user_email'    => [
                'type' => 'text',
                'required' => true
            ]
        ],
        'block' => [
            'user_id',
            'user_name',
            'forgot_code',
            'account_code'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'login' => REQUIRE_LOGOUT,
        'verify' => [
            'block' => '\Block\User',
            'method' => 'getByEmail',
            'selector' => 'user_email'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if ($this->data->get('account_code')) {
            throw new \Exception\Notice('account_not_activated');
        }

        // GENERATE CODE
        $code = !$this->data->get('forgot_code') ? substr(md5(RAND), 0, 15) : $this->data->get('forgot_code');

        if (!$this->data->get('forgot_code')) {
            // INSERT CODE TO DATABASE
            $this->db->insert(TABLE_FORGOT, [
                'user_id' => $this->data->get('user_id'),
                'forgot_code' => $code
            ]);
        }

        // SEND MAIL WITH CODE
        $mail = new MailForgot();
        $mail->mail->addAddress($this->data->get('user_email'), $this->data->get('user_name'));
        $mail->assign(['code' => $code]);
        $mail->send();
    }
}