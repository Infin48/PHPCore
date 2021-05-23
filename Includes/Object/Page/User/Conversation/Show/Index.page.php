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
use Block\ConversationMessage;

use Model\Pagination;
use Model\Database\Query;

use Visualization\Panel\Panel;
use Visualization\Block\Block;
use Visualization\Sidebar\Sidebar;
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
        'id' => int,
        'editor' => EDITOR_BIG,
        'template' => 'User/Conversation/Show',
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
        $_conversation = new Conversation();
        $query = new Query();
        $conversationMessage = new ConversationMessage();

        // CONVERSATION DATA
        $conversation = $_conversation->get($this->getID()) or $this->error();

        // HEAD
        $this->data->head = [
            'title'         => $conversation['conversation_name'],
            'description'   => $conversation['conversation_text']
        ];

        // ASSIGN DATA TO TEMPLATE
        $this->data->data([
            'conversation_name' => $conversation['conversation_name']
        ]);

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('User/Conversation');
        $this->data->breadcrumb = $breadcrumb->getData();

        // CONVERSATION RECIPIENTS
        $recipients = $_conversation->getRecipients($this->getID());

        // IF IS THIS UNREAD CONVERSATION
        if (in_array($conversation['conversation_id'], $unread = $this->user->get('unread')) === true) {
            unset($unread[array_search($conversation['conversation_id'], $unread)]);
            $this->user->set('unread', $unread);

            $query->query('
                DELETE unr FROM ' . TABLE_USERS_UNREAD . '
                WHERE conversation_id = ? AND user_id = ?
            ', [$conversation['conversation_id'], LOGGED_USER_ID]);
        }
        
        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_MESSAGES);
        $pagination->total($conversation['conversation_messages']);
        $pagination->url($this->getURL());
        $conversationMessage->pagination = $this->data->pagination = $pagination->getData();

        // PANEL
        $panel = new Panel('Conversation');
        if ($conversation['user_id'] == LOGGED_USER_ID) $panel->object('tools')->row('edit')->show();
        $this->data->panel = $panel->getData();

        // BLOCK
        $block = new Block('Conversation');
        $block->object('conversation')->appTo($conversation);
        $block->object('conversationmessage')->fill($conversationMessage->getParent($this->getID()));
        $block->object('conversationmessage')->row('bottom')->show();

        if (PAGE == 1) {
            $block->object('conversation')->show();
        }

        $this->data->block = $block->getData();

        // SIDEBAR
        $sidebar = new Sidebar('Conversation');
        $sidebar->small();
        $sidebar->object('info')
            ->row('messages')->value($conversation['conversation_messages'])
            ->row('recipients')->value(count($recipients))
            ->object('recipients')->fill($recipients);

        if (count($recipients) >= 10 or $conversation['user_id'] != LOGGED_USER_ID) {
            $sidebar->row('bottom')->hide();
        }

        $this->data->sidebar = $sidebar->getData();

        // LEAVE CONVERSATION
        $this->process->call(type: 'Conversation/Leave', url: '/user/conversation/', on: $this->url->is('leave'), data: [
            'conversation_id' => $conversation['conversation_id'],
            'recipients' => array_column($recipients, 'user_id')
        ]);

        // MARK AS UNREAD CONVERSATION
        $this->process->call(type: 'Conversation/Mark', on: $this->url->is('mark'), data: [
            'conversation_id' => $conversation['conversation_id'],
        ]);

        if ($conversation['user_id'] == LOGGED_USER_ID) {

            // ADD RECIPIENT
            $this->process->form(type: 'Conversation/Recipient', data: [
                'conversation_id' => $conversation['conversation_id'],
            ]);
        }
    }
}