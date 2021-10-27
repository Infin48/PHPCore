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

namespace Page\User\Conversation;

use Block\User;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Add
 */
class Add extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'editor' => EDITOR_BIG,
        'template' => '/User/Conversation/New',
        'loggedIn' => true
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BLOCK
        $user = new User();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/User/Conversation');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/User/Conversation');
        $field->object('conversation')
            ->row('add_recipient')->show()
            ->row('recipients')->show();
        $this->data->field = $field->getData();

        // IF RECIPIENT IS DEFINED IN URL
        if ($this->url->is('to') and $this->url->get('to') != LOGGED_USER_ID) {
            if ($data = $user->get((int)$this->url->get('to'))) {
                $this->data->data['recipient'] = $data;
            }
        }

        // NEW CONVERSATION
        $this->process->form(type: '/Conversation/Create');
    }
}