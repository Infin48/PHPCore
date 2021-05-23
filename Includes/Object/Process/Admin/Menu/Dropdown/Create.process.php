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

namespace Process\Admin\Menu\Dropdown;

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
            'button_name' => [
                'type' => 'text',
                'required' => true
            ],
            'button_icon' => [
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
        // UPDATE POSITION INDEX
        $this->db->update(TABLE_BUTTONS, ['position_index' => [PLUS]]);

        // ADDS BUTTON
        $this->db->insert(TABLE_BUTTONS, [
            'button_name'       => $this->data->get('button_name'),
            'is_dropdown'       => '1',
            'button_icon'       => $this->data->get('button_icon'),
            'button_icon_style' => $this->data->get('button_icon_style')
        ]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('button_name'));
    }
}