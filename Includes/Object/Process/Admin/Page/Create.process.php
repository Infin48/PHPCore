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
 * Create
 */
class Create extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'page_name' => [
                'type' => 'text',
                'required' => true
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
        $this->db->insert(TABLE_PAGES, [
            'page_name' => $this->data->get('page_name'),
            'page_url' => parse($this->data->get('page_name'))
        ]);
        @mkdir(ROOT . '/Pages/' . $this->db->lastInsertId());

        @file_put_contents(ROOT . '/Pages/' . $this->db->lastInsertId() . '/html.html', '');
        @file_put_contents(ROOT . '/Pages/' . $this->db->lastInsertId() . '/css.css', '');

        // ADD RECORD TO LOG
        $this->log($this->data->get('page_name'));
    }
}