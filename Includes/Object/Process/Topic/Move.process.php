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
 * Move
 */
class Move extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'new_forum_id' => [
                'type' => 'int',
                'required' => true,
                'block' => '\Block\Forum.getAllToMoveID'
            ]
        ],
        'data' => [
            'user_id', // TOPIC USER ID
            'topic_id',
            'topic_name',
            'current_forum_id' // NEW FORUM ID
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
        if ($this->data->get('current_forum_id') == $this->data->get('new_forum_id')) {
            return true;
        }

        // UPDATE STATISTICS IN OLD FORUM
        $this->db->query('
            UPDATE ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET f.forum_topics = f.forum_topics - 1,
                f.forum_posts = f.forum_posts - t.topic_posts
            WHERE t.topic_id = ?
        ', [$this->data->get('topic_id')]);

        // UPDATE STATISTICS IN NEW FORUM
        $this->db->query('
            UPDATE ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = ?
            SET f.forum_topics = f.forum_topics + 1,
                f.forum_posts = f.forum_posts + t.topic_posts,
                t.forum_id = ?
            WHERE t.topic_id = ?
        ', [$this->data->get('new_forum_id'), $this->data->get('new_forum_id'), $this->data->get('topic_id')]);

        // SET NEW FORUM ID TO POSTS
        $this->db->query('
            UPDATE ' . TABLE_POSTS . '
            SET p.forum_id = ?
            WHERE p.topic_id = ?
        ', [$this->data->get('new_forum_id'), $this->data->get('topic_id')]);

        // SEND NOTIFICATION
        $this->notifi(
            id: $this->data->get('topic_id'),
            to: (int)$this->data->get('user_id'),
            replace: true
        );

        // ADD RECORD TO LOG
        $this->log($this->data->get('topic_name'));
    }
}