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
 * Permission
 */
class Permission extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'category_permission_see'  => [
                'type' => 'array',
                'block' => '\Block\Group.getAllIDWithVisitor'
            ]
        ],
        'data' => [
            'category_id'
        ],
        'block' => [
            'category_name'
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
        // DELETE CATEGORY PERMISSIONS
        $this->db->query('
            DELETE cps FROM ' . TABLE_CATEGORIES_PERMISSION_SEE . '
            WHERE category_id = ?
        ', [$this->data->get('category_id')]);

        foreach ((array)$this->data->get('category_permission_see') ?: [] as $groupID) {

            // ADD PERMISSION
            $this->db->insert(TABLE_CATEGORIES_PERMISSION_SEE, [
                'category_id' => $this->data->get('category_id'),
                'group_id' => $groupID
            ]);
        }

        // ADD RECORD TO LOG
        $this->log($this->data->get('category_name'));
    }
}