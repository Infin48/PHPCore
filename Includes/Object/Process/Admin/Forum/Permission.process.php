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
 * Permission
 */
class Permission extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'forum_permission_see'      => [
                'type' => 'array',
                'block' => '\Block\Group.getAllIDWithVisitor'
            ],
            'forum_permission_post'     => [
                'type' => 'array',
                'block' => '\Block\Group.getAllIDWithVisitor'
            ],
            'forum_permission_topic'    => [
                'type' => 'array',
                'block' => '\Block\Group.getAllIDWithVisitor'
            ]
        ],
        'data' => [
            'forum_id'
        ],
        'block' => [
            'forum_name',
            'forum_link'
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
        // DELETE FORUM PERMISSIONS SEE
        $this->db->query('
            DELETE fps FROM ' . TABLE_FORUMS_PERMISSION_SEE . '
            WHERE forum_id = ?
        ', [$this->data->get('forum_id')]);


        // INSERT FORUM PERMISSION TO SEE
        foreach ((array)$this->data->get('forum_permission_see') ?: [] as $groupID) {
            $this->db->insert(TABLE_FORUMS_PERMISSION_SEE, [
                'forum_id' =>  $this->data->get('forum_id'),
                'group_id' => $groupID
            ]);
        }

        // DELETE FORUM PERMISSIONS POST
        $this->db->query('
            DELETE fpp FROM ' . TABLE_FORUMS_PERMISSION_POST . '
            WHERE forum_id = ?
        ', [$this->data->get('forum_id')]);

        // DELETE FORUM PERMISSIONS TOPIC
        $this->db->query('
            DELETE fpt FROM ' . TABLE_FORUMS_PERMISSION_TOPIC . '
            WHERE forum_id = ?
        ', [$this->data->get('forum_id')]);

        if (empty($this->data->get('forum_link'))) {

            foreach ($this->data->get('forum_permission_post') ?: [] as $groupID) {

                // INSERT FORUM PERMISSIONS TO CREATE POSTS
                $this->db->insert(TABLE_FORUMS_PERMISSION_POST, [
                    'forum_id' => $this->data->get('forum_id'),
                    'group_id' => $groupID
                ]);
            }

            foreach ($this->data->get('forum_permission_topic') ?: [] as $groupID) {
                
                // INSERT FORUM PERMISSIONS TO CREATE TOPICS
                $this->db->insert(TABLE_FORUMS_PERMISSION_TOPIC, [
                    'forum_id' => $this->data->get('forum_id'),
                    'group_id' => $groupID
                ]);
            }
        }

        // ADD RECORD TO LOG
        $this->log($this->data->get('forum_name'));
    }
}