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

namespace Process\Admin\User;

/**
 * Promote
 */
class Promote extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'user_id'
        ],
        'block' => [
            'user_name'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\User',
            'method' => 'get',
            'selector' => 'user_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->query('
            UPDATE ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.is_admin = 1
            SET u.is_admin = 1,
                u2.is_admin = 0
            WHERE u.user_id = ?
        ', [$this->data->get('user_id')]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('user_name'));

        $this->redirectTo('/admin/user/');
    }
}