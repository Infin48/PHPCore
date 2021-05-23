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

namespace Process\Topic;

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
            'topic_id'
        ],
        'block' => [
            'user_id',
            'forum_id',
            'forum_url',
            'topic_name'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Topic',
            'method' => 'get',
            'selector' => 'topic_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->insert(TABLE_DELETED_CONTENT, [
            'user_id' => LOGGED_USER_ID,
            'deleted_type' => 'Topic',
            'deleted_type_id' => $this->data->get('topic_id'),
            'deleted_type_user_id' => $this->data->get('user_id')
        ]);

        $this->id = $this->db->lastInsertID();
            
        // SET TOPIC AS DELETED
        $this->db->query('
            UPDATE ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET f.forum_posts = f.forum_posts - t.topic_posts,
            f.forum_topics = f.forum_topics - 1,
            t.deleted_id = ?
            WHERE t.topic_id = ?
        ', [$this->id, $this->data->get('topic_id')]);

        // SEND NOTIFICATION
        $this->notifi(
            id: $this->data->get('topic_id'),
            to: $this->data->get('user_id'),
            replace: false
        );

        // ADD RECORD TO LOG
        $this->log($this->data->get('topic_name'));

        $this->redirectTo('/forum/show/' . $this->data->get('forum_id') . '.' . $this->data->get('forum_url') . '/');
    }
}