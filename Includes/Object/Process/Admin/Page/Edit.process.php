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

namespace Process\Admin\Page;

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
            'page_name' => [
                'type' => 'text',
                'required' => true
            ],
            'page_css'  => [
                'type' => 'text'
            ],
            'page_html' => [
                'type' => 'clear'
            ]
        ],
        'data' => [
            'page_id'
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
        @file_put_contents(ROOT . '/Pages/' . $this->data->get('page_id') . '/html.html', $this->data->get('page_html'));
        @file_put_contents(ROOT . '/Pages/' . $this->data->get('page_id') . '/css.css', $this->data->get('page_css'));

        // EDIT PAGE
        $this->db->update(TABLE_PAGES, [
            'page_url' => parse($this->data->get('page_name')),
            'page_name' => $this->data->get('page_name')
        ], $this->data->get('page_id'));

        // ADD RECORD TO LOG
        $this->log($this->data->get('page_name'));
    }
}