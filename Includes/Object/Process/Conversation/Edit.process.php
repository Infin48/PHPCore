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

namespace Process\Conversation;

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
            'conversation_name'    => [
                'type' => 'text',
                'required' => true,
                'length_max' => 50
            ],
            'conversation_text'       => [
                'type' => 'html',
                'required' => true,
                'length_max' => 10000
            ],
        ],
        'data' => [
            'conversation_id'
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
        $this->db->update(TABLE_CONVERSATIONS, [
            'conversation_url'      => parse($this->data->get('conversation_name')),
            'conversation_text' 	=> $this->data->get('conversation_text'),
            'is_edited'             => '1',
            'conversation_edited'   => DATE_DATABASE,
            'conversation_name' 	=> $this->data->get('conversation_name'),
        ], $this->data->get('conversation_id'));
    }
}