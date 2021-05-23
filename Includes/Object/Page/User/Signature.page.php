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
 * Signature
 */
class Signature extends \Page\Page
{    
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'editor' => EDITOR_MEDIUM,
        'template' => 'User/Signature',
        'loggedIn' => true
    ];
    
    /**
     * Body of page
     *
     * @return void
     */
    protected function body()
    {
        // BREADCRUMB
        $breadcrumb = new Breadcrumb('User/Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('User/Signature');
        $field->data($this->user->get());
        $this->data->field = $field->getData();

        // SIDEBAR
        $sidebar = new Sidebar('User');
        $sidebar->left();
        $sidebar->small();
        $this->data->sidebar = $sidebar->getData(); 

        // PROCESS
        $this->process->form(type: 'User/Signature');
    }
}