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

namespace Process\Admin\Group;

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
            'group_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Group',
            'method' => 'get',
            'selector' => 'group_id'
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
            UPDATE ' . TABLE_GROUPS . '
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_index = g.group_index + 1
            SET g.group_index = g.group_index + 1,
                g2.group_index = g2.group_index - 1
            WHERE g.group_id = ? AND g2.group_id IS NOT NULL AND g.group_index < ' . LOGGED_USER_GROUP_INDEX . ' AND g2.group_index < ' . LOGGED_USER_GROUP_INDEX
        , [$this->data->get('group_id')]);

        // ADD RECORD TO LOG
        $this->log();
    }
}