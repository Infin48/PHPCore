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

namespace Process\Admin\Category;

/**
 * Up
 */
class Up extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'category_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Admin\Category',
            'method' => 'get',
            'selector' => 'category_id'
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
            UPDATE ' . TABLE_CATEGORIES . '
            LEFT JOIN ' . TABLE_CATEGORIES . '2 ON c2.position_index = c.position_index + 1
            SET c.position_index = c.position_index + 1,
                c2.position_index = c2.position_index - 1
            WHERE c.category_id = ? AND c2.category_id IS NOT NULL
        ', [$this->data->get('category_id')]);

        // ADD RECORD TO LOG
        $this->log();
    }
}