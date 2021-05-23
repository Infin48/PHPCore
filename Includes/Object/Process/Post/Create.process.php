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
 * Create
 */
class Create extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [

            // POST TEXT
            'text' => [
                'type' => 'html',
                'required' => true,
                'length_max' => 10000,
            ],
        ],
        'data' => [
            'topic_id'
        ],
        'block' => [
            'user_id',
            'forum_id',
            'is_locked',
            'post_permission'
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
        // IF USER DOESN'T HAVE PERMISSION TO CREATE NEW POST
        if ($this->data->get('post_permission') == 0) {
            return false;
        }

        // IF TOPIC IS LOCKED
        if ($this->data->get('is_locked') == 1) {
            return false;
        }
            
        // INSERTS POST TO DATABASE
        $this->db->insert(TABLE_POSTS, [
            'topic_id'      => $this->data->get('topic_id'),
            'post_text'     => $this->data->get('text'),
            'user_id'  		=> LOGGED_USER_ID,
            'forum_id'      => $this->data->get('forum_id')
        ]);

        $this->id = $this->db->lastInsertId();

        // UPDATES USER NUMBER OF POSTS
        $this->db->query('
            UPDATE ' . TABLE_USERS . '
            SET user_posts = user_posts + 1
            WHERE user_id = ?
        ', [LOGGED_USER_ID]);

        // UPDATES NUMBER OF POSTS IN TOPIC AND FORUM
        $this->db->query('
            UPDATE ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET topic_posts = topic_posts + 1,
                forum_posts = forum_posts + 1
            WHERE topic_id = ?
        ', [$this->data->get('topic_id')]);

        // SEND USER NOTIFICATION
        $this->notifi(
            id: $this->id,
            to: $this->data->get('user_id')
        );
    }
}