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

namespace Process\Post;

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
            'post_id'
        ],
        'block' => [
            'user_id',
            'topic_name',
            'post_permission'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Post',
            'method' => 'get',
            'selector' => 'post_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if ($this->data->get('post_permission') != 1) {
            return false;
        }

        $this->db->insert(TABLE_DELETED_CONTENT, [
            'user_id' => LOGGED_USER_ID,
            'deleted_type' => 'Post',
            'deleted_type_id' => $this->data->get('post_id'),
            'deleted_type_user_id' => $this->data->get('user_id')
        ]);

        self::$id = $this->db->lastInsertID();

        $this->db->query('
            UPDATE ' . TABLE_POSTS . '
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET topic_posts = topic_posts - 1,
                forum_posts = forum_posts - 1,
                p.deleted_id = ?
            WHERE p.post_id = ?
        ', [self::$id, $this->data->get('post_id')]);

        // SEND USER NOTIFICATION
        $this->notifi(
            id: $this->data->get('post_id'),
            to: $this->data->get('user_id')
        );

        // ADD RECORD TO LOG
        $this->log($this->data->get('topic_name'));
    }
}