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

namespace Page\Install;

/**
 * Admin
 */
class Admin extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Install/Admin'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $this->data->breadcrumb = [
            'list' => [
                'install-language',
                'database',
                'install-admin',
                'install-site',
                'end',
            ],
            'active' => [
                'install-language',
                'database',
                'install-admin',
            ]
        ];

        // SETUP DATABASE
        $this->process->form(type: '/Admin');
    }
}