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

use Model\File;

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
            'topic_name'            => [
                'type' => 'text',
                'required' => true,
                'length_max' => 100
            ],
            'topic_text'            => [
                'type' => 'html',
                'required' => true,
                'length_max' => 100000
            ],
            'topic_label'           => [
                'type' => 'array',
                'length_max' => 5,
                'block' => '\Block\Label.getAllID'
            ],
            'delete_topic_image'    => [
                'type' => 'checkbox',
            ]
        ],
        'data' => [
            'forum_id',
        ],
        'block' => [
            'category_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'permission' => 'topic.create',
        'verify' => [
            'block' => '\Block\Forum',
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
        $file = new File();

        // IF LOGGED USER HAS PERMISSION TO UPLOAD TOPIC IMAGE
        if ($this->perm->has('topic.image')) {

            // LOAD TOPIC IMAGE
            $file->load('topic_image');
        }

        // INSERT TOPIC
        $this->db->insert(TABLE_TOPICS, [
            'user_id'       => LOGGED_USER_ID,
            'forum_id'      => $this->data->get('forum_id'),
            'topic_url'     => parse($this->data->get('topic_name')),
            'topic_text'    => $this->data->get('topic_text'),
            'topic_name'    => $this->data->get('topic_name'),
            'topic_image'   => $file->check() ? $file->getFormat() . '?' . RAND : '',
            'category_id'   => $this->data->get('category_id')
        ]);

        // STORE INSERTED ID
        $id = $this->db->lastInsertId();

        // IF LOGGED USER HAS PERMISISON TO ADD LABELS
        if ($this->perm->has('topic.label')) {
            
            foreach ($this->data->get('topic_label') as $labelID) {

                // INSERT LABEL TO TOPIC
                $this->db->insert(TABLE_TOPICS_LABELS, [
                    'topic_id' => $id,
                    'label_id' => $labelID
                ]);
            }
        }

        // UPDATES USER NUMBER OF TOPICS
        $this->db->update(TABLE_USERS, [
            'user_topics' => [PLUS],
        ], LOGGED_USER_ID);

        // UPDATES USER NUMBER OF TOPICS IN FORUM
        $this->db->update(TABLE_FORUMS, [
            'forum_topics' => [PLUS],
        ],$this->data->get('forum_id'));

        // UPLOAD IMAGE
        $file->upload('/Topic/' . $id);

        // SETS REDIRECT URL
        $this->redirectTo('/forum/topic/' . $id . '.' . parse($this->data->get('topic_name')));
    }
}