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
 * Create
 */
class Create extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'button_sub_name'   => [
                'type' => 'text',
                'required' => true
            ],
            'button_sub_link_type'  => [
                'custom' => [1, 2]
            ],
            'button_sub_link'   => [
                'type' => 'text',
                'required' => true
            ]
        ],
        'data' => [
            'button_id'
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
        // UPDATE POSITION INDEX
        $this->db->query('
            UPDATE ' . TABLE_BUTTONS_SUB . '
            SET position_index = position_index + 1
            WHERE button_id = ?
        ', [$this->data->get('button_id')]);

        $this->db->insert(TABLE_BUTTONS_SUB, [
            'button_id'             => $this->data->get('button_id'),
            'position_index'        => '1',
            'button_sub_name'       => $this->data->get('button_sub_name'),
            'button_sub_link'       => $this->data->get('button_sub_link'),
            'button_sub_link_type'  => $this->data->get('button_sub_link_type')
        ]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('button_sub_name'));
    }
}