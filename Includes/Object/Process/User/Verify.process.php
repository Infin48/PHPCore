<?php

/**
 * This file is part of the PHPCore Forum Software
 * 
 * Made by InfinCZ 
 * @link https://github.com/Infin48
 *
 * @copyright (c) PHPCore Limited https://phpcore.cz
 * @license GNU General Public License, version 3 (GPL-3.0)
 */

namespace Process\User;

/**
 * Verify
 */
class Verify extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'verify_code',
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
            'method' => 'getByVerifyCode',
            'selector' => 'verify_code'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->query('DELETE v FROM ' . TABLE_VERIFY . ' WHERE user_id = ?', [$this->data->get('user_id')]);
    }
}