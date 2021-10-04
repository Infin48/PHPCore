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

use Model\File\File;

/**
 * Edit
 */
class Edit extends \Process\ProcessExtend
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
            'delete_topic_image'    => [
                'type' => 'checkbox'
            ]
        ],
        'data' => [
            'topic_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        // FILE
        $file = new File();
        
        // IF LOGGED USER HAS PERMISSION TO UPLOAD TOPIC IMAGE
        if ($this->perm->has('topic.image')) {

            // IF DELETE TOPIC IMAGE BUTTON WAS PRESSED
            if ($this->data->is('delete_topic_image')) {

                // DELETE IMAGE
                $file->deleteImage('/Topic/' . $this->data->get('topic_id'));

                // DELETE TOPIC IMAGE
                $this->db->update(TABLE_TOPICS, [
                    'topic_image' => ''
                ], $this->data->get('topic_id'));

            } else {

                // UPLOAD TOPIC IMAGE
                $image = $file->form('topic_image', FILE_TYPE_IMAGE);

                if ($image->check()) {

                    // DELETE OLD IMAGE
                    $file->deleteImage('/Topic/' . $this->data->get('topic_id'));

                    // UPLOAD IMAGE
                    $image->upload('/Uploads/Topic/' . $this->data->get('topic_id'));


                    // SET TOPIC IMAGE
                    $this->db->update(TABLE_TOPICS, [
                        'topic_image' => $image->getFormat() . '?' . RAND
                    ], $this->data->get('topic_id'));
                }
            }
        }

        // UPDATE TOPIC
        $this->db->update(TABLE_TOPICS, [
            'topic_url'         => parse($this->data->get('topic_name')),
            'topic_text'        => $this->data->get('topic_text'),
            'topic_name'        => $this->data->get('topic_name'),
            'topic_edited'      => '1',
            'topic_edited_at'   => DATE_DATABASE
        ], $this->data->get('topic_id'));
    }
}