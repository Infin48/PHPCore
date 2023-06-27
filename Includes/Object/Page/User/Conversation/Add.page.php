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

namespace App\Page\User\Conversation;

/**
 * Add
 */
class Add extends \App\Page\Page
{
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
    protected string $template = 'Root/Style:/Templates/User/Conversation/New.phtml';
    
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

        // If is enabled blog mode
        if ($system->get('site_mode') == 'blog')
        {
            // Show 404 error page
            $this->error404();
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/User/Conversations.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Conversation.json');
        $form
            ->form('conversation')
                ->callOnSuccess($this, 'newConversation')
                ->frame('conversation')
                    ->input('add_recipient')->show()
                    ->input('to')->show();
        $data->form = $form->getDataToGenerate();

        $data->set('data.recipient', []);
        // If recipient is defined in url
        if ($this->url->is('to'))
        {
            // If recipient in URL is not logged user
            if ($this->url->get('to') != LOGGED_USER_ID)
            {
                // If recipient exists
                if ($_ = $db->select('app.user.get()', (int)$this->url->get('to')))
                {
                    $data->set('data.recipient.' . mt_rand(), $_);
                }
            }
        }
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
    public function newConversation( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Insert conversation to database
        $db->insert(TABLE_CONVERSATIONS, [
            'conversation_text'		=> $post->get('conversation_text'),
            'user_id'	            => LOGGED_USER_ID,
            'conversation_name'	    => $post->get('conversation_name')
        ]);

        $lastInsertId = $db->lastInsertId();

        // Insert conversation URL
        $db->update(TABLE_CONVERSATIONS, [
            'conversation_url' => $lastInsertId . '.' . parse($post->get('conversation_name'))
        ], $lastInsertId);


        foreach (array_merge([LOGGED_USER_ID], array_unique($post->get('to'))) as $userID)
        {
            // Add recipient
            $db->insert(TABLE_CONVERSATIONS_RECIPIENTS, [
                'conversation_id' => $lastInsertId,
                'user_id' => $userID
            ]);

            if ($userID != LOGGED_USER_ID)
            {
                // Uploads user's new message notifications
                $db->insert(TABLE_USERS_UNREAD, [
                    'conversation_id' => $lastInsertId,
                    'user_id' => $userID
                ]);
            }
        }

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect
        $data->set('data.redirect', '/user/conversation/show/' . $lastInsertId . '.' . parse($post->get('conversation_name')));
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
        // If number of max recipients is not reached
        if (count($post->get('list') ?: []) >= 9)
        {
            throw new \App\Exception\Notice('to_length_max');
        }

        // Get all users from list
        $users = $db->query('
            SELECT user_id, user_name, user_profile_image, group_class, user_deleted
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            WHERE (FIND_IN_SET(user_id, "' . implode(',', $post->get('list') ?: []) . '") OR user_name = ?) AND user_id <> ? 
        ', [$post->get('user_name'), LOGGED_USER_ID], ROWS);

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( url: '/Includes/Object/Visualization/Form/Templates/User/Conversation/Recipient.phtml', data: ['recipient' => $users] )
        ];
    }
}