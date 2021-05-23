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

use Visualization\Field\Field;

/**
 * Login
 */
class Login extends Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'Login',
        'loggedOut' => true
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // FIELD
        $field = new Field('User/Login');
        $this->data->field = $field->getData();

        // LOGIN USER
        $this->process->form(type: 'User/Login', url: '/');
    }
}