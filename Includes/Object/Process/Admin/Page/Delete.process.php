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
 * Delete
 */
class Delete extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'page_id'
        ],
        'block' => [
            'page_name'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Page',
            'method' => 'get',
            'selector' => 'page_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        @unlink(ROOT . '/Pages/' .  $this->data->get('page_id') . '/html.html');
        @unlink(ROOT . '/Pages/' .  $this->data->get('page_id') . '/css.css');
        @rmdir(ROOT . '/Pages/' . $this->data->get('page_id'));

        $this->db->query('DELETE pg FROM ' . TABLE_PAGES . ' WHERE page_id = ?', [$this->data->get('page_id')]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('page_name'));

        // REFRESH PAGE
        $this->refresh();
    }
}