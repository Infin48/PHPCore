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

namespace Page\User;

use Visualization\Field\Field;
use Visualization\Sidebar\Sidebar;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Account
 */
class Account extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/User/Account',
        'loggedIn' => true
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/User/Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/User/Account');
        $field->data($this->user->get());
        $this->data->field = $field->getData();

        // SIDEBAR
        $sidebar = new Sidebar('/User');
        $sidebar->left();
        $sidebar->small();
        $sidebar->object('basic')->row('account')->select();
        $this->data->sidebar = $sidebar->getData();

        // PROCESS
        $this->process->form(type: '/User/Account', data: [
            'email_code' => $this->user->get('email_code'),
            'current_user_email' => $this->user->get('user_email'),
            'current_user_password' => $this->user->get('user_password')
        ]);
    }
}