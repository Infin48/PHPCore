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

namespace Process\Admin\Settings\URL;

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
            'settings_url_from'    => [
                'type' => 'text',
                'required' => true
            ],
            'settings_url_to'   => [
                'type' => 'text',
                'required' => true
            ],
            'settings_url_hidden' => [
                'type' => 'checkbox'
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
        if (
            !str_starts_with($this->data->get('settings_url_from'), '/') or
            !str_ends_with($this->data->get('settings_url_from'), '/') or
            !str_starts_with($this->data->get('settings_url_to'), '/') or
            !str_ends_with($this->data->get('settings_url_to'), '/')
        ) {
            throw new \Exception\Notice('settings_url_start_with_slash');
        }

        $this->db->insert(TABLE_SETTINGS_URL, [
            'settings_url_from'         => $this->data->get('settings_url_from'),
            'settings_url_to'           => $this->data->get('settings_url_to'),
            'settings_url_hidden'       => (int)$this->data->get('settings_url_hidden')
        ]);

        // ADD RECORD TO LOG
        $this->log();
    }
}