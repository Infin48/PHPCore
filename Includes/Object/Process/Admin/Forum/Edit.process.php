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
 * Edit
 */
class Edit extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'forum_name' => [
                'type' => 'text',
                'required' => true
            ],
            'forum_description' => [
                'type' => 'text',
                'required' => true
            ],
            'forum_main' => [
                'type' => 'checkbox'
            ],
            'category_id_new' => [
                'type' => 'number',
                'block' => '\Block\Admin\Category.GetAllID'
            ],
            'enable_link' => [
                'type' => 'radio'
            ],
            'forum_link' => [
                'type' => 'text'
            ],
            'forum_icon' => [
                'type'  => 'text'
            ],
            'forum_icon_style' => [
                'custom'  => ['fas', 'far', 'fab']
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
        if ($this->data->get('category_id_new') != $this->data->get('category_id')) {

            $this->db->query('
                UPDATE ' . TABLE_FORUMS . '
                LEFT JOIN ' . TABLE_FORUMS . '2 ON f2.category_id = ?
                LEFT JOIN ' . TABLE_FORUMS . '3 ON f3.category_id = f.category_id AND f3.position_index > f.position_index
                SET f.category_id = ?,
                    f.position_index = 1,
                    f2.position_index = f2.position_index + 1,
                    f3.position_index = f3.position_index - 1
                WHERE f.forum_id = ?
            ', [$this->data->get('category_id_new'), $this->data->get('category_id_new'), $this->data->get('forum_id')]);

        }
        
        $isMain = $this->data->get('forum_main');
        if ($this->data->is('enable_link')) {
            $isMain = 0;
        } 

        if ($this->data->is('forum_main')) {
            $this->db->update(TABLE_FORUMS, ['forum_main' => '0']);
        }
        
        // UPDATE FORUM
        $this->db->update(TABLE_FORUMS, [
            'forum_main'        => $isMain,
            'forum_link'        => $this->data->is('enable_link') ? $this->data->get('forum_link') : '',
            'forum_url'         => parse($this->data->get('forum_name')),
            'forum_name'        => $this->data->get('forum_name'),
            'forum_icon'        => $this->data->get('forum_icon'),
            'forum_icon_style'  => $this->data->get('forum_icon_style'),
            'forum_description' => $this->data->get('forum_description')
        ], $this->data->get('forum_id'));
        
        // ADD RECORD TO LOG
        $this->log($this->data->get('forum_name'));
    }
}