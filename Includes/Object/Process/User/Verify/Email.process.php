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

namespace Process\User\Verify;

/**
 * Email
 */
class Email extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'email_code',
        ],
        'block' => [
            'user_id',
            'user_email'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\User',
            'method' => 'getByEmailCode',
            'selector' => 'email_code'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->query('DELETE ve FROM ' . TABLE_VERIFY_EMAIL . ' WHERE user_id = ?', [$this->data->get('user_id')]);

        // UPDATE USER INFORMATIONS
        $this->db->update(TABLE_USERS, [
            'user_email' 	=> $this->data->get('user_email')
        ], $this->data->get('user_id'));
    }
}