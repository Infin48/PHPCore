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
 * Edit
 */
class Edit extends \App\Page\Page
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
    protected string $template = 'Root/Style:/Templates/User/Conversation/Edit.phtml';

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
        if ($system->get('site.mode') == 'blog')
        {
            // Show 404 error page
            $this->error404();
        }

        // Conversation data
        $row = $db->select('app.conversation.get()', $this->url->getID()) or $this->error404();

        // Save conversation data
        $data->set('data.conversation', $row);

        // If this conversation is not from logged user
        if ($data->get('data.conversation.user_id') != LOGGED_USER_ID)
        {
            redirect('/user/conversation/');
        }
        
        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/User/Conversations.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.conversation.conversation_name'))->href($this->build->url->conversation($data->get('data.conversation')));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Conversation.json');
        $form
            ->form('conversation')
                ->data($data->get('data.conversation'))
                ->callOnSuccess($this, 'editConversation');
        $data->form = $form->getDataToGenerate();

        // Head
        $data->set('data.head.title', $data->get('data.conversation.conversation_name'));
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
    public function editConversation( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Update conversation
        $db->update(TABLE_CONVERSATIONS, [
            'conversation_url'          => $data->get('data.conversation.conversation_id') . '.' . parse($post->get('conversation_name')),
            'conversation_text' 	    => $post->get('conversation_text'),
            'conversation_name' 	    => $post->get('conversation_name'),
            'conversation_edited'       => '1',
            'conversation_edited_at'    => DATE_DATABASE,
        ], $data->get('data.conversation.conversation_id'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect to conversation
        $data->set('data.redirect', '/user/conversation/show/' . $data->get('data.conversation.conversation_id') . '.' . parse($post->get('conversation_name')));
    }
}