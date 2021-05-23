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

use Block\Conversation;

use Model\Pagination;

use Visualization\Lists\Lists;
use Visualization\Panel\Panel;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Index
 */
class Index extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'User/Conversation/Index',
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

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('User/Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // PANEL
        $panel = new Panel('ConversationList');
        $this->data->panel = $panel->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_PRIVATE_MESSAGES);
        $pagination->total($conversation->getAllCount());
        $pagination->url($this->getURL());
        $conversation->pagination = $this->data->pagination = $pagination->getData();

        // LIST
        $list = new Lists('Conversation');

        foreach ($conversation->getAll() as $item) {

            $list->object('conversation')->appTo($item)->jumpTo();

            if (in_array($item['conversation_id'], $this->user->get('unread'))) {
                $list->select();
            }
        }
        $this->data->list = $list->getData();
    }
}