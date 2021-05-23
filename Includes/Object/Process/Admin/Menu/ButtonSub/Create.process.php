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
            'is_external_link'  => [
                'type' => 'radio'
            ],
            'button_sub_link'   => [
                'type' => 'text'
            ],
            'page_id'           => [
                'type' => 'number',
                'block' => '\Block\Page.getAllID'
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
        if (empty($this->data->get('page_id')) && empty($this->data->get('button_sub_link'))) {
            throw new \Exception\Notice('enter_correct_link');
        }

        // UPDATE POSITION INDEX
        $this->db->query('
            UPDATE ' . TABLE_BUTTONS_SUB . '
            SET position_index = position_index + 1
            WHERE button_id = ?
        ', [$this->data->get('button_id')]);

        $this->db->insert(TABLE_BUTTONS_SUB, [
            'page_id'           => $this->data->is('is_external_link') ? '' : $this->data->get('page_id'),
            'button_id'         => $this->data->get('button_id'),
            'position_index'    => '1',
            'button_sub_name'   => $this->data->get('button_sub_name'),
            'button_sub_link'   => $this->data->is('is_external_link') ? $this->data->get('button_sub_link') : '',
            'is_external_link'  => $this->data->get('is_external_link')
        ]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('button_sub_name'));
    }
}