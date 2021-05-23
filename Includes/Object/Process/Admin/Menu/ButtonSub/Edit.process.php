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
 * Edit
 */
class Edit extends \Process\ProcessExtend
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
            'button_sub_id'
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

        $this->db->update(TABLE_BUTTONS_SUB, [
                'page_id'           => $this->data->is('is_external_link') ? null : $this->data->get('page_id'),
                'button_sub_name'   => $this->data->get('button_sub_name'),
                'button_sub_link'   => $this->data->is('is_external_link') ? $this->data->get('button_sub_link') : '',
                'is_external_link'  => $this->data->get('is_external_link')
        ], $this->data->get('button_sub_id'));

        // ADD RECORD TO LOG
        $this->log($this->data->get('button_sub_name'));
    }
}