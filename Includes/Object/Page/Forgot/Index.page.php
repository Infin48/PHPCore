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

namespace Page\Forgot;

use Visualization\Field\Field;

/**
 * Index
 */
class Index extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'Forgot/Send',
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
        $field = new Field('User/Forgot/Send');
        $this->data->field = $field->getData();

        // SEND FORGOT PASSWORD MAIL
        $this->process->form(type: 'Forgot/Send', url: '/'); 
    }
}