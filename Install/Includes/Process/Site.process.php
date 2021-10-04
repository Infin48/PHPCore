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
        $JSON = new JSON('/Install/Includes/Settings.json');
        
        $this->db->table(TABLE_SETTINGS, [
            'site.language' => $JSON->get('language'),
            'site.started' => DATE_DATABASE,
            'site.name' => $this->data->get('name'),
            'site.updated' => DATE_DATABASE,
            'site.description' => $this->data->get('description')
        ]);

        $JSON->set('db', true);
        $JSON->set('page', 'end');
        $JSON->set('back', false);
        $JSON->save();
    }
}