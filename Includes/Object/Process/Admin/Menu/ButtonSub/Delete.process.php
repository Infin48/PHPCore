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

namespace Process\Admin\Menu\ButtonSub;

/**
 * Delete
 */
class Delete extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'button_sub_id'
        ],
        'block' => [
            'button_sub_name'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\ButtonSub',
            'method' => 'get',
            'selector' => 'button_sub_id'
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
            UPDATE ' . TABLE_BUTTONS_SUB . '
            LEFT JOIN ' . TABLE_BUTTONS_SUB . '2 ON bs2.position_index > bs.position_index AND bs2.button_id = bs.button_id
            SET bs2.position_index = bs2.position_index - 1
            WHERE bs.button_sub_id = ?
        ', [$this->data->get('button_sub_id')]);

        $this->db->query('DELETE bs FROM ' . TABLE_BUTTONS_SUB . ' WHERE button_sub_id = ?', [$this->data->get('button_sub_id')]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('button_sub_name'));

        // REFRESH PAGE
        $this->refresh();
    }
}