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

 namespace App\Page\Get;

/**
 * Message
 */
class Message extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // Language
        $language = $data->get('inst.language');

        // Form
        $post = new \App\Model\Post;

        // Message data
        $row = $db->select('app.message.get()', $post->get('id')) or $this->error404();

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Conversation.json');
        $block->elm1('conversationmessage')->appTo(data: $row, function: function ( \App\Visualization\Block\Block $block ) use ($post, $language)
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

            // If message is selected
            if ($post->get('selected'))
            {
                $block->select();
            }
        });

        $data->block = $block->getDataToGenerate();
        
        $this->data = $data;

        $this->path = new \App\Model\Path();
        $this->language = $language;

        require $this->path->build('Root/Style:/Templates/Blocks/Visualization/Block/Block.phtml');

        exit();
    }
}