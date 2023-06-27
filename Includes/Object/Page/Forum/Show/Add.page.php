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

namespace App\Page\Forum\Show;

/**
 * Add
 */
class Add extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Forum/Topic/New.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'topic.create';

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

        // User permission
        $permission = $user->get('permission');

        // If is enabled blog mode
        if ($system->get('site_mode') == 'blog')
        {
            // Show 404 error page
            $this->error404();
        }

        // Forum
        $row = $db->select('app.forum.get()', $this->url->getID()) or $this->error404();

        // Save forum data
        $data->set('data.forum', $row);

        // If logged user doesn't have permission to see this forum or create topic
        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.forum.permission_see')) or !array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.forum.permission_topic')))
        {
            // Show 404 error page
            $this->error404();
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Forum.json');
        $breadcrumb->elm1('category')->title($data->get('data.forum.category_name'))->up()
            ->create()->jumpTo()->title($data->get('data.forum.forum_name'))->href($this->build->url->forum($data->get('data.forum')));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Topic.json');
        $form
            ->form('topic')
                ->callOnSuccess($this, 'newTopic')
                ->frame('topic')

                    // Setup row with topic header image uploading
                    ->input('topic_image', function ( \App\Visualization\Form\Form $form ) use ($permission)
                    {
                        // If logged user has permission to upload topic image
                        if ($permission->has('topic.image')) {
                            
                            // Show 'upload topic image' form 
                            $form->show();
                        }
                    })
                    // Setup row with labels
                    ->input('topic_label', function ( \App\Visualization\Form\Form $form ) use ($permission)
                    {
                        // If logged user has permission to marks topics with labels 
                        if ($permission->has('topic.label')) {
                            
                            // Show 'labels list
                            $form->show();
                        }
                    })
                    // Fill row with labels
                    ->input('topic_label')->fill(data: $db->select('app.label.all()'), function: function ( \App\Visualization\Form\Form $form )
                    {
                        $form->set('data.value', $form->get('data.label_id'));
                    });
        $data->form = $form->getDataToGenerate();

        // Head
        $data->set('data.head.title', $data->get('data.forum.forum_description'));
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
    public function newTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Insert topic
        $db->insert(TABLE_TOPICS, [
            'user_id'       	=> LOGGED_USER_ID,
            'forum_id'      	=> $data->get('data.forum.forum_id'),
            'topic_text'    	=> $post->get('topic_text'),
            'topic_name'    	=> $post->get('topic_name'),
            'category_id'   	=> $data->get('data.forum.category_id')
        ]);

        // Store inserted id
        $id = $db->lastInsertId();

        $file = new \App\Model\File\File();
        $file->mkdir('/Uploads/Topics/' . $id);
        $file->mkdir('/Uploads/Topics/' . $id . '/Images');
        $file->mkdir('/Uploads/Topics/' . $id . '/Attachments');

        // Save every uploaded attachment
		foreach ($post->get('topic_attachments') as $attachment)
		{
            $attachment->upload('/Uploads/Topics/' . $id . '/Attachments');
		}

        // Save every uploaded image
		foreach ($post->get('topic_images') as $image)
		{
            $image->compress(50);
            $image->upload('/Uploads/Topics/' . $id . '/Images');
		}

        // If logged user has permisison to add labels
        if ($permission->has('topic.label'))
        {    
            foreach ($post->get('topic_label') as $labelID)
            {
                // Insert label to topic
                $db->insert(TABLE_TOPICS_LABELS, [
                    'topic_id' => $id,
                    'label_id' => $labelID
                ]);
            }
        }

        // Updates user number of topics
        $db->update(TABLE_USERS, [
            'user_topics' => [PLUS],
        ], LOGGED_USER_ID);

        // Updates user number of topics in forum
        $db->update(TABLE_FORUMS, [
            'forum_topics' => [PLUS],
        ], $data->get('data.forum.forum_id'));

        // If logged user has permission to upload topic image
        $format = '';
        if ($permission->has('topic.image'))
        {
            $image = $post->get('topic_image');
            // If user uploaded image
            if ($image->exists())
            {
                // Compress
                $image->compress(75);

                // Upload image
                $image->upload('/Uploads/Topics/' . $id, 'Header');
                
                $format = $image->getFormat() . '?' . mt_rand();
            }
        }

        // Edit topic url adn image
        $db->update(TABLE_TOPICS, [
            'topic_url'     => $id . '.' . parse($post->get('topic_name')),
            'topic_image'   => $format,
            'topic_attachments' => count($post->get('topic_attachments')) + count($post->get('topic_images'))
        ], $id);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Sets redirect url
        $data->set('data.redirect', '/forum/topic/' . $id . '.' . parse($post->get('topic_name')));
    }
}