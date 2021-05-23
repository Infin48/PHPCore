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

namespace Process\Admin\Menu\Button;

/**
 * Up
 */
class Up extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'button_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Button',
            'method' => 'get',
            'selector' => 'button_id'
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
            UPDATE ' . TABLE_BUTTONS . '
            LEFT JOIN ' . TABLE_BUTTONS . '2 ON b2.position_index = b.position_index + 1
            SET b.position_index = b.position_index + 1,
                b2.position_index = b2.position_index - 1
            WHERE b.button_id = ? AND b2.button_id IS NOT NULL
        ', [$this->data->get('button_id')]);

        // ADD RECORD TO LOG
        $this->log();
    }
}