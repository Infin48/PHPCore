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

namespace Page\Admin\Settings;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Email
 */
class Email extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Overall',
        'permission' => 'admin.settings'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('settings')->active()->option('email')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/Admin/Settings/Email');
        $field->data($this->system->get());
        $this->data->field = $field->getData();

        // SEND TEST EMAIL
        $this->process->form(type: '/Admin/Settings/EmailSend', on: 'send');

        // EDIT SETTINGS
        $this->process->form(type: '/Admin/Settings/Email');
    }
}