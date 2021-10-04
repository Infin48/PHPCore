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
 * Delete
 */
class Delete extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'group_id'
        ],
        'block' => [
            'group_name'
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
        if ($this->system->get('default_group') == $this->data->get('group_id')) {
            return false;
        }

        $this->db->query('
            UPDATE ' . TABLE_GROUPS . '
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_index > g.group_index
            LEFT JOIN ' . TABLE_USERS . ' ON u.group_id = g.group_id
            SET g2.group_index = g2.group_index - 1,
                u.group_id = ?
            WHERE g.group_id = ?
        ', [$this->system->get('default_group'), $this->data->get('group_id')]);

        $this->db->query('
            DELETE g, cps, fps, fpp, fpt
            FROM ' . TABLE_GROUPS. ' 
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.group_id = g.group_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.group_id = g.group_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_POST . ' ON fpp.group_id = g.group_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_TOPIC . ' ON fpt.group_id = g.group_id
            WHERE g.group_id = ?
        ', [$this->data->get('group_id')]);

        // UPDATE GROUPS SESSION
        $this->db->table(TABLE_SETTINGS, [
            'session.groups' => RAND
        ]);
        
        // ADD RECORD TO LOG
        $this->log($this->data->get('group_name'));

        // REFRESH PAGE
        $this->refresh();
    }
}