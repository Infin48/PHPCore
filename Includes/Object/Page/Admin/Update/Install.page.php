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

namespace Page\Admin\Update;

/**
 * Install
 */
class Install extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'permission' => 'admin.settings'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        extract($this->language->get());

        require ROOT . '/Includes/Update/html.phtml';
        exit();
    }
}