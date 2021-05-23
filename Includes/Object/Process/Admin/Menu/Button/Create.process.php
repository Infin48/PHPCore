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
            'is_external_link'  => [
                'type' => 'radio'
            ],
            'button_link'       => [
                'type' => 'text'
            ],
            'page_id'           => [
                'type' => 'number',
                'block' => '\Block\Page.getAllID'
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
        if (empty($this->data->get('page_id')) && empty($this->data->get('button_link'))) {
            throw new \Exception\Notice('enter_correct_link');
        }

        $this->db->update(TABLE_BUTTONS, ['position_index' => [PLUS]]);

        $this->db->insert(TABLE_BUTTONS, [
            'page_id'           => $this->data->is('is_external_link') ? null :$this->data->get('page_id'),
            'button_name'       => $this->data->get('button_name'),
            'button_link'       => $this->data->is('is_external_link') ? $this->data->get('button_link') : '',
            'button_icon'       => $this->data->get('button_icon'),
            'position_index'    => '1',
            'button_icon_style' => $this->data->get('button_icon_style'),
            'is_external_link'  => $this->data->get('is_external_link')
        ]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('button_name'));
    }
}