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

namespace Process\Admin\Settings;

/**
 * Index
 */
class Index extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'site_name'         => [
                'type' => 'text',
                'required' => true
            ],
            'site_description'  => [
                'type' => 'text',
                'required' => true
            ],
            'site_keywords'  => [
                'type' => 'text',
                'required' => true
            ],
            'image_max_size'    => [
                'type' => 'number',
                'required' => true
            ],
            'cookie_enabled'    => [
                'type' => 'checkbox'
            ],
            'cookie_text'       => [
                'type' => 'text'
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
        $this->db->table(TABLE_SETTINGS, [
            'site.name' => $this->data->get('site_name'),
            'site.description' => $this->data->get('site_description'),
            'site.keywords' => $this->data->get('site_keywords'),
            'image.max_size' => (int)$this->data->get('image_max_size'),
            'cookie.enabled' => (int)$this->data->get('cookie_enabled'),
            'cookie.text' => $this->data->get('cookie_text')
        ]);
        
        $this->updateSession();

        // ADD RECORD TO LOG
        $this->log();
    }
}