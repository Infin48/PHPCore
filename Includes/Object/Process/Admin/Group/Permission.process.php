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
 * Permission
 */
class Permission extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'group_permission'  => [
                'type' => 'array',
                'block' => '\Model\Permission.getPermissions'
            ]
        ],
        'data' => [
            'group_id',
            'group_name'
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
        $this->db->update(TABLE_GROUPS, [
            'group_permission'  => implode(',', $this->data->get('group_permission'))
        ], $this->data->get('group_id'));

        // ADD RECORD TO LOG
        $this->log($this->data->get('group_name'));
    }
}