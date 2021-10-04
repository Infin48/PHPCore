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

namespace Page;

use Model\JSON;

/**
 * Database
 */
class Database extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Database'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $JSON = new JSON('/Install/Includes/Settings.json');

        if ($JSON->get('operation') === 'install') {

            $this->data->breadcrumb = [
                'list' => [
                    'install-language',
                    'database',
                    'install-admin',
                    'install-site',
                    'end'
                ],
                'active' => [
                    'install-language',
                    'database'
                ]
            ];
        } else {

            $this->data->breadcrumb = [
                'list' => [
                    'database',
                    'update',
                    'end',
                ],
                'active' => [
                    'database'
                ]
            ];
        }

        // SETUP DATABASE
        $this->process->form(type: '/Database');
    }
}