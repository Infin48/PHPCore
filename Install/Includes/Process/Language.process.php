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
 * Language
 */
class Language extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'language'      => [
                'type' => 'string',
                'required' => true
            ]
        ],
        'data' => [
            'languageList'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if (in_array($this->data->get('language'), $this->data->get('languageList'))) {

            $this->system->install([
                'db' => false,
                'page' => 2
            ]);

            $this->system->set('site.language', $this->data->get('language'));
        }
    }
}