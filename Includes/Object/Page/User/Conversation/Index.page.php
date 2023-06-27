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
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/User/Conversation/Index.phtml';
    
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

        // If is enabled blog mode
        if ($system->get('site_mode') == 'blog')
        {
            // Show 404 error page
            $this->error404();
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/User/Conversations.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Panel
        $panel = new \App\Visualization\Panel\Panel('Root/Panel:/Formats/ConversationList.json');
        $data->panel = $panel->getDataToGenerate();

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_PRIVATE_MESSAGES);
        $pagination->total($db->select('app.conversation.count()'));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // List
        $list = new \App\Visualization\Lists\Lists('Root/Lists:/Formats/Conversation.json');
        // Setup conversations
        $list->elm1('conversation')->fill(data: $db->select('app.conversation.all()'), function: function ( \App\Visualization\Lists\Lists $list ) use ($user)
        {
            // Define variables
            $list
                // data.name = Name of conversation
                ->set('data.link', '<a href="' . $this->build->url->conversation($list->get('data')) . '">' . $list->get('data.conversation_name') . '</a>')
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $list->get('data')))
                // data.date - Date of creating conversation
                ->set('data.date', $this->build->date->long($list->get('data.conversation_created')))
                // data.user_image - Image of user
                ->set('data.user_image', $this->build->user->image(data: $list->get('data'), role: true));

            // If conversation contains any message
            if ($list->get('data.conversation_message_id'))
            {
                // Get from the whole data only data which is regarding to this message
                $data = getKeysWithPrefix($list->get('data'), prefix: 'message_');
                $list
                    // data.lastpost.date - Date of creating message
                    ->set('data.lastpost.date', $this->build->date->long($list->get('data.conversation_message_created'), true))
                    // data.lastpost.user = Link to user
                    ->set('data.lastpost.user', $this->build->user->link(data: $data))
                    // data.lastpost.user_image = Image of user
                    ->set('data.lastpost.user_image', $this->build->user->image(data: $data, role: true));
            }

            // If is this conversation in user's unreaded
            if (in_array($list->get('data.conversation_id'), $user->get('unread')))
            {
                // Select this row(conversation)
                $list->select();
            }
        });

        // Finish list and get ready to generate
        $data->list = $list->getDataToGenerate();
    }
}