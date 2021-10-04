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

namespace Page\User\Conversation\Show;

use Block\Conversation;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Edit
 */
class Edit extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'editor' => EDITOR_BIG,
        'template' => 'User/Conversation/Edit',
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
        $conversation = new Conversation();

        // CONVERSATION DATA
        $conversation = $conversation->get($this->url->getID()) or $this->error();

        // IF THIS CONVERSATION IS NOT FOR MINE
        if ($conversation['user_id'] != LOGGED_USER_ID) redirect('/user/conversation/');

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/User/Conversation');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/User/Conversation');
        $field->data($conversation);
        $this->data->field = $field->getData();

        // EDIT CONVERSATION
        $this->process->form(type: '/Conversation/Edit', data: [
            'conversation_id' => $conversation['conversation_id']
        ]);

        // HEAD
        $this->data->head['title'] = $conversation['conversation_name'];
    }
}