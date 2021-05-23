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

namespace Process\Admin\Category;

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
            'category_name'         => [
                'type' => 'text',
                'required' => true
            ],
            'category_description'  => [
                'type' => 'text',
                'required' => true
            ],
            'see_groups'            => [
                'type' => 'array',
                'block' => '\Block\Group.getAllIDWithVisitor'
            ]
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
        // POSITION INDEX
        $this->db->query('
            UPDATE ' . TABLE_CATEGORIES . '
            SET position_index = position_index + 1
        ');

        // ADD CATEGORY
        $this->db->insert(TABLE_CATEGORIES, [
            'category_name'         => $this->data->get('category_name'),
            'position_index'        => '1',
            'category_description'  => $this->data->get('category_description')
        ]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('category_name'));
    }
}