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

use Model\JSON;

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


            $JSON = new JSON('/Install/Includes/Settings.json');
            $JSON->set('db', false);
            $JSON->set('page', 'database');
            $JSON->set('language', $this->data->get('language'));
            $JSON->set('back', false);
            $JSON->save();
        }
    }
}