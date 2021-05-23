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

namespace Process;

/**
 * Site
 */
class Site extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'name'      => [
                'type' => 'text',
                'required' => true
            ],
            'description'  => [
                'type' => 'text',
                'required' => true
            ]
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $settings = [];
        $settings['site.started'] = DATE_DATABASE;
        $settings['site.name'] = $this->data->get('name');
        $settings['site.updated'] = DATE_DATABASE;
        $settings['site.description'] = $this->data->get('description');
        $this->system->set($settings);

        $this->system->install([
            'db' => true,
            'page' => 6
        ]);
    }
}