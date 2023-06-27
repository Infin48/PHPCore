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

namespace App\Page\User\Conversation\Show;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/User/Conversation/Show.phtml';

    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    public function ajax( string $ajax )
    {
        return match($ajax)
        {
            'run/add-recipient' => 'addRecipient',

            'run/conversation/leave' => 'leaveConversation',
            'run/conversation/mark-as-unread' => 'markConversationAsUnread',
            
            'run/message/edit' => 'editMessage',
            'run/message/create' => 'newMessage',
            'run/message/editor' => 'editorMessage',

            default => ''
        };
    }

    /**
     * Load data according to received ajax
     *
     * @param  string $ajax Received ajax
     * 
     * @return array Data
     */
    public function ajaxData( string $ajax )
    {
        return match($ajax)
        {
            'run/add-recipient' => [
                'user_name' => STRING
            ],

            'run/message/edit',
            'run/message/create' => [
                'id' => STRING,
                'text' => STRING
            ],

            default => []
        };
    }

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // Language
        $language = $data->get('inst.language');

        // If is enabled blog mode
        if ($system->get('site_mode') == 'blog')
        {
            // Show 404 error page
            $this->error404();
        }

        // Conversation data
        $row = $db->select('app.conversation.get()', $this->url->getID()) or $this->error404();

        // Save conversation data
        $data->set('data.conversation', $row);

        // Head
        $data->set('data.head.title', $data->get('data.conversation.conversation_name'));
        $data->set('data.head.description', $data->get('data.conversation.conversation_text'));

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/User/Conversations.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.conversation.conversation_name'))->href($this->build->url->conversation($data->get('data.conversation')));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Conversation recipients
        $recipients = $db->select('app.conversation.recipients()', $this->url->getID());

        // Save recipients
        $data->set('data.conversation.recipients', $recipients);

        // If logged user has this conversation as unreaded
        if (in_array($data->get('data.conversation.conversation_id'), $unread = $user->get('unread')) === true)
        {
            unset($unread[array_search($data->get('data.conversation.conversation_id'), $unread)]);

            // Change number of unreaded conversations in navbar
            $navbar = new \App\Visualization\Navbar\Navbar($data->navbar);
            $navbar->elm1('logged')->elm2('conversation')->set('data.notifiCount', count((array)$unread));
            $data->navbar = $navbar->getDataToGenerate();

            // Delete this conversation from unreaded
            $db->query('
                DELETE unr FROM ' . TABLE_USERS_UNREAD . '
                WHERE conversation_id = ? AND user_id = ?
            ', [$data->get('data.conversation.conversation_id'), LOGGED_USER_ID]);
        }
        
        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_MESSAGES);
        $pagination->total($data->get('data.conversation.conversation_messages'));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // Panel
        $panel = new \App\Visualization\Panel\Panel('Root/Panel:/Formats/Conversation.json');

        $panel
            // Add ID to panel
            // This is for ajax requests
            ->id($data->get('data.conversation.conversation_id'))
            // Set position to edit button in panel
            ->elm1('tools')->elm2('edit', function ( \App\Visualization\Panel\Panel $panel ) use ($data)
            {
                // If logged user is founder of this conversation
                if ($data->get('data.conversation.user_id') == LOGGED_USER_ID)
                {
                    // Show this button
                    $panel->show();
                }
            });

        // Finish panel and get ready to generate
        $data->panel = $panel->getDataToGenerate();

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Conversation.json');
        $block
            // Set position to block which is used for creating new message in conversation
            ->elm1('conversationmessage')->elm2('bottom')->show()
            // Set id to this row
            ->set('data.html.ajax-id', $data->get('data.conversation.conversation_id'))
            // And also name
            ->set('data.conversation_name', $data->get('data.conversation.conversation_name'));

        // Setup conversation
        $block->elm1('conversation')->appTo(data: $data->get('data.conversation'), function: function ( \App\Visualization\Block\Block $block )
        {
            // Define variables
            $block
                // data.name = Name of conversation
                ->set('data.name', $block->get('data.conversation_name'))
                // data.text = Text of conversation
                ->set('data.text', $block->get('data.conversation_text'))
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $block->get('data')))
                // data.group = Group of user
                ->set('data.group', $this->build->user->group(data: $block->get('data')))
                // data.date - Date of creating conversation
                ->set('data.date', $this->build->date->long($block->get('data.conversation_created'), true))
                // data.user_image - Image of user
                ->set('data.user_image', $this->build->user->image(data: $block->get('data'), role: true, online: true, size: '50x50'))
                // Save edited date to variable as default
                ->set('data.edited', $this->build->date->long($block->get('data.conversation_edited_at')));

            // But if this conversation wasn't edited yet
            if ($block->get('data.conversation_edited') == 0)
            {
                // Erase it
                $block->set('data.edited', '');
            }

            // If user is on first page of pagination
            if (PAGE == 1)
            {
                // Show conversation
                $block->elm1('conversation')->show();
            }
        });
        $block->elm1('conversationmessage')->fill(data: $db->select('app.message.parent()', $this->url->getID()), function: function ( \App\Visualization\Block\Block $block ) use ($language)
        {
            // Define variables
            $block
                // data.name = Name of conversation
                ->set('data.name', $language->get('L_RE') . ': ' . $block->get('data.conversation_name'))
                // data.html.ajax-id - Sets id to "ajax-id" attribute
                ->set('data.html.ajax-id', $block->get('data.conversation_message_id'))
                // data.text = Text of conversation
                ->set('data.text', $block->get('data.conversation_message_text'))
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $block->get('data')))
                // data.group = Group of user
                ->set('data.group', $this->build->user->group(data: $block->get('data')))
                // data.date - Date of creating conversation
                ->set('data.date', $this->build->date->long($block->get('data.conversation_message_created'), true))
                // data.user_image - Image of user
                ->set('data.user_image', $this->build->user->image(data: $block->get('data'), role: true, online: true, size: '50x50'))
                // data.user_signature - Remove signature from conversations
                ->set('data.user_signature', '')
                // Save edited date to variable as default
                ->set('data.edited', $this->build->date->long($block->get('data.conversation_message_edited_at')));

            // But if this message wasn't edited yet
            if ($block->get('data.conversation_message_edited') == 0)
            {
                // Erase date of last edit
                $block->set('data.edited', '');
            }
            
            // If this message is from logged user
            if ($block->get('data.user_id') == LOGGED_USER_ID)
            {
                // Show 'edit' button
                $block->show('data.button.edit');
            }
        });

        // Finish block and get ready to generate
        $data->block = $block->getDataToGenerate();

        // Sidebar
        $sidebar = new \App\Visualization\Sidebar\Sidebar('Root/Sidebar:/Formats/Conversation.json');
        $sidebar
            // Set sidebar to small version
            ->small()
            // Set position to information widget
            ->elm1('info')
                ->elm2('table')
                    // Set number of created messages
                    ->elm3('messages')->value($data->get('data.conversation.conversation_messages'))
                    // Set number of recipients
                    ->elm3('recipients')->value(count($recipients))
                    // Add list of recipients
                    ->elm1('recipients')->fill(data: $recipients)
                // Set position to bottom
                // where is input to adding new recipient
                ->elm2('bottom', function ( \App\Visualization\Sidebar\Sidebar $sidebar ) use ($recipients, $data)
                {
                    // If in this conversation is already 10 added recipients
                    // or logged user is not founder of this conversation
                    if (count($recipients) >= 10 or $data->get('data.conversation.user_id') != LOGGED_USER_ID)
                    {
                        // Hide this row(this input to adding new recipient)
                        $sidebar->hide();
                    }
                });

        // Finish sidebar and get ready to generate
        $data->sidebar = $sidebar->getDataToGenerate();
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function addRecipient( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $user = $db->select('app.user.byName()', $post->get('user_name')) or $this->error404();

        // If added recipient is logge user
        if (LOGGED_USER_ID == $user['user_id'])
        {
            throw new \App\Exception\Notice('conversation_user_myself');
        }

        // If addred recipient is already between recipients
        if (in_array($user['user_id'], array_column($data->get('data.conversation.recipients'), 'user_id')))
        {
            throw new \App\Exception\Notice('conversation_user_exist');
        }

        // If recipients is already more than 10
        if (count($data->get('data.conversation.recipients')) >= 10)
        {
            return false;
        }

        // Add recipient
        $db->insert(TABLE_CONVERSATIONS_RECIPIENTS, [
            'conversation_id' => $data->get('data.conversation.conversation_id'),
            'user_id' => $user['user_id']
        ]);

        // Set conversation as unreaded for new recipient
        $db->insert(TABLE_USERS_UNREAD, [
            'conversation_id' => $data->get('data.conversation.conversation_id'),
            'user_id' => $user['user_id']
        ]);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function newMessage( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // HTML Purifier
        $HTMLPurifier = new \App\Model\HTMLPurifier('big');

        // Insert message
        $db->insert(TABLE_CONVERSATIONS_MESSAGES, [
            'conversation_id'			=> $post->get('id'),
            'user_id' 		            => LOGGED_USER_ID,
            'conversation_message_text'	=> $HTMLPurifier->purify($post->get('text'))
        ]);

        $id = $db->lastInsertId();

        // Edit private message
        $db->update(TABLE_CONVERSATIONS, [
            'conversation_messages' => [PLUS],
        ], $post->get('id'));

        // Get unread users
        $unread = array_column($db->query('
            SELECT user_id
            FROM ' . TABLE_USERS_UNREAD . '
            WHERE conversation_id = ?
        ', [$post->get('form')], ROWS), 'user_id');

        // Set unread to recipients
        foreach (array_column($data->get('data.conversation.recipients'), 'user_id') as $userID)
        {
            if ($userID != LOGGED_USER_ID)
            {
                if (in_array($userID, $unread) === false)
                {
                    // Uploads user's new message notifications
                    $db->insert(TABLE_USERS_UNREAD, [
                        'conversation_id' => $post->get('id'),
                        'user_id' => $userID
                    ]);
                }
            }
        }

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'message', id: $id )
        ];
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function editorMessage( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Content
        $content = new \App\Model\Content();
        
        return [
            'trumbowyg' => $data->get('data.trumbowyg.big'),
            'button' => $content->get(
                url: 'Root/Style:/Templates/Blocks/Visualization/Block/Buttons/Save.phtml'
            )
        ];
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function editMessage( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // HTML Purifier
        $HTMLPurifier = new \App\Model\HTMLPurifier('big');

        // Edit message
        $db->update(TABLE_CONVERSATIONS_MESSAGES, [
            'conversation_message_text'	        => $HTMLPurifier->purify($post->get('text')),
            'conversation_message_edited'       => '1',
            'conversation_message_edited_at'    => DATE_DATABASE
        ], $post->get('id'));

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'message', id: $post->get('id') )
        ];
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function leaveConversation( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Delete logged user from conversation recipients
        $db->query('
            DELETE cr FROM ' . TABLE_CONVERSATIONS_RECIPIENTS . '
            WHERE conversation_id = ? AND user_id = ?
        ', [$data->get('data.conversation.conversation_id'), LOGGED_USER_ID]);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/user/conversation/');
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function markConversationAsUnread( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Set conversation as unreaded
        $db->insert(TABLE_USERS_UNREAD, [
            'conversation_id' => $data->get('data.conversation.conversation_id'),
            'user_id' => LOGGED_USER_ID
        ]);

        // Redirect user
        $data->set('data.redirect', '/user/conversation/');
    }
}