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

namespace Process\Admin\Forum;

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
            'forum_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Admin\Forum',
            'method' => 'get',
            'selector' => 'forum_id'
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
            UPDATE ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS . '2 ON f2.position_index = f.position_index + 1 AND f2.category_id = f.category_id
            SET f.position_index = f.position_index + 1,
                f2.position_index = f2.position_index - 1
            WHERE f.forum_id = ? AND f2.forum_id IS NOT NULL
        ', [$this->data->get('forum_id')]);

        // ADD RECORD TO LOG
        $this->log();
    }
}