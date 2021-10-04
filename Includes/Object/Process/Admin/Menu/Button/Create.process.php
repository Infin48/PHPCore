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
 * Create
 */
class Create extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'button_name'       => [
                'type' => 'text',
                'required' => true
            ],
            'button_link_type'  => [
                'custom' => [1, 2]
            ],
            'button_link'       => [
                'type' => 'text',
                'required' => true
            ],
            'button_icon'       => [
                'type' => 'text'
            ],
            'button_icon_style' => [
                'custom'  => ['fas', 'far', 'fab']
            ]
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
        $this->db->update(TABLE_BUTTONS, ['position_index' => [PLUS]]);

        $this->db->insert(TABLE_BUTTONS, [
            'button_name'       => $this->data->get('button_name'),
            'button_link'       => $this->data->get('button_link'),
            'button_icon'       => $this->data->get('button_icon'),
            'position_index'    => '1',
            'button_link_type'  => $this->data->get('button_link_type'),
            'button_icon_style' => $this->data->get('button_icon_style')
        ]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('button_name'));
    }
}