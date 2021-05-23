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
 * Account
 */
class Account extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'account_code',
        ],
        'block' => [
            'user_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'login' => REQUIRE_LOGOUT,
        'verify' => [
            'block' => '\Block\User',
            'method' => 'getByAccountCode',
            'selector' => 'account_code'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->query('DELETE va FROM ' . TABLE_VERIFY_ACCOUNT . ' WHERE user_id = ?', [$this->data->get('user_id')]);
    }
}