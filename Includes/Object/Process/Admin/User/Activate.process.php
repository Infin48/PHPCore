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
 * Activate
 */
class Activate extends \Process\ProcessExtend
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
        $this->db->query('DELETE va FROM ' . TABLE_VERIFY_ACCOUNT . ' WHERE user_id = ?', [$this->data->get('user_id')]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('user_name'));
    }
}