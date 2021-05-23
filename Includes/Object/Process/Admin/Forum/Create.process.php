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
 * Create
 */
class Create extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'forum_name'            => [
                'type' => 'text',
                'required' => true
            ],
            'forum_description'     => [
                'type' => 'text',
                'required' => true
            ],
            'enable_link'           => [
                'type' => 'radio'
            ],
            'forum_link'            => [
                'type' => 'text'
            ],
            'is_main'               => [
                'type' => 'checkbox'
            ],
            'forum_icon'            => [
                'type'  => 'text'
            ],
            'forum_icon_style'      => [
                'custom'  => ['fas', 'far', 'fab']
            ]
        ],
        'data' => [
            'category_id'
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
        // UPDATE POSITION INDEX
        $this->db->query('
            UPDATE ' . TABLE_FORUMS . '
            SET position_index = position_index + 1
            WHERE category_id = ?
        ', [$this->data->get('category_id')]);

        if ($this->data->is('is_main')) {
            $this->db->query('UPDATE ' . TABLE_FORUMS . ' SET is_main = 0');
        }

        $this->db->insert(TABLE_FORUMS, [
            'is_main'           => (int)$this->data->get('is_main'),
            'forum_url'         => parse($this->data->get('forum_name')),
            'forum_link'        => $this->data->get('forum_link'),
            'forum_name'        => $this->data->get('forum_name'),
            'forum_icon'        => $this->data->get('forum_icon'),
            'category_id'       => $this->data->get('category_id'),
            'forum_icon_style'  => $this->data->get('forum_icon_style'),
            'forum_description' => $this->data->get('forum_description')
        ]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('forum_name'));
    }
}