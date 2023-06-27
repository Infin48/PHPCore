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

namespace App\Page\Forum\Topic;

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
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * @var bool $photoSwipe If true - JS library PhotoSwipe will be loaded 
     */
    protected bool $photoSwipe = true;

    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Forum/Topic/Edit.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'topic.edit';

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

        // Topic
        $row = $db->select('app.topic.get()', $this->url->getID()) or $this->error404();

        // Save topic data
        $data->set('data.topic', $row);

        // Topic is not from logged user
        if ($data->get('data.topic.user_id') != LOGGED_USER_ID)
        {
            // Show 404 error page
            $this->error404();
        }

        // If logged user doesn't have permission to see this forum or edit topic
        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_see')) or !array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
        {
            // Show 404 error page
            $this->error404();
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Forum.json');
        $breadcrumb->elm1('category')->title($data->get('data.topic.category_name'))->up()
            ->create()->jumpTo()->title($data->get('data.topic.forum_name'))->href($this->build->url->forum($data->get('data.topic')))->up()
            ->create()->jumpTo()->title($data->get('data.topic.topic_name'))->href($this->build->url->topic( data: $data->get('data.topic')));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // File model
        $file = new \App\Model\File\File();

        $images = [];
        // Search fimages
        $file->getFiles(
            path: '/Uploads/Topics/' . $this->url->getID() . '/Images/*',
            function: function ( \App\Model\File\File $file, string $path ) use (&$images)
            {
                $size = getimagesize($path);
                $images[] = [
                    'type' => 'image',
                    'path' => str_replace(ROOT, '', $path),

                    // Set default sizes for SVG images
                    'width' => $size[0] ?? 1920,
                    'height' => $size[1] ?? 1080
                ];
            }
        );

        $attachments = [];
        // Search attachments
        $file->getFiles(
            path: '/Uploads/Topics/' . $this->url->getID() . '/Attachments/*',
            flag: \App\Model\File\File::SORT_BY_DATE,
            function: function ( \App\Model\File\File $file, string $path ) use (&$attachments)
            {
                $ex = explode('/', str_replace(ROOT, '', $path));
                $filter = array_filter($ex);

                $attachments[] = [
                    'name' => array_pop($filter),
                    'path' => str_replace(ROOT, '', $path)
                ];
            }
        );

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Topic.json');
        $form
            ->form('topic')
                ->callOnSuccess($this, 'editTopic')
                // Set data to form
                ->data($data->get('data.topic'))
                ->frame('topic')
                    // Setup row with topic header image uploading
                    ->input('topic_image', function ( \App\Visualization\Form\Form $form ) use ($permission)
                    {
                        // If logged user has permission to upload topic image
                        if ($permission->has('topic.image'))
                        {    
                            // Show 'upload topic image' input 
                            $form->show();
                        }
                    })
                    // Setup row with deleteing header image
                    ->input('delete_topic_image', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        // If header image exists
                        if ($data->get('data.topic.topic_image'))
                        {    
                            // Show 'delete image' checkbox
                            $form->show();
                        }
                    })
                    ->input('topic_images')->fill($images)
                    ->input('topic_attachments')->fill($attachments);
        $data->form = $form->getDataToGenerate();

        // Head
        $data->set('data.head.title', $data->get('data.topic.topic_name'));
        $data->set('data.head.description', $data->get('data.topic.topic_text'));
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
    public function editTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // User
        $user = $data->get('inst.user');

        // System
        $system = $data->get('inst.system');

        // User permission
        $permission = $user->get('permission');

        // File
        $file = new \App\Model\File\File();
        $file->mkdir('/Uploads/Topics/' . $data->get('data.topic.topic_id'));
        $file->mkdir('/Uploads/Topics/' . $data->get('data.topic.topic_id') . '/Images');
        $file->mkdir('/Uploads/Topics/' . $data->get('data.topic.topic_id') . '/Attachments');
        
        // If logged user has permission to upload topic image
        if ($permission->has('topic.image'))
        {
            // If delete topic image button was pressed
            if ($post->get('delete_topic_image'))
            {
                // Delete image
                $file->delete('/Uploads/Topics/' . $data->get('data.topic.topic_id') . '/Header.*');

                // Update topic image
                $db->update(TABLE_TOPICS, [
                    'topic_image' => ''
                ], $data->get('data.topic.topic_id'));

            } else {

                // Upload topic image
                $image = $post->get('topic_image');
                if ($image->exists())
                {
                    // Delete old image
                    $file->delete('/Uploads/Topics/' . $data->get('data.topic.topic_id') . '/Header.*');

                    // Compress
                    $image->compress(75);

                    // Upload image
                    $image->upload('/Uploads/Topics/' . $data->get('data.topic.topic_id'), 'Header');

                    // Update topic image
                    $db->update(TABLE_TOPICS, [
                        'topic_image' => $image->getFormat() . '?' . mt_rand()
                    ], $data->get('data.topic.topic_id'));
                }
            }
        }

        // Save every uploaded attachment
		foreach ($post->get('topic_attachments') as $attachment)
		{
            $attachment->upload('/Uploads/Topics/' . $data->get('data.topic.topic_id') . '/Attachments');
		}

        // Save every uploaded image
		foreach ($post->get('topic_images') as $image)
		{
            $image->compress(50);
            $image->upload('/Uploads/Topics/' . $data->get('data.topic.topic_id') . '/Images');
		}

        // List of attachments
        $attachments = $file->getFiles( path: '/Uploads/Topics/' . $data->get('data.topic.topic_id') . '/Attachments/*' );

        // List of images
        $images = $file->getFiles( path: '/Uploads/Topics/' . $data->get('data.topic.topic_id') . '/Images/*' );

        // Update number of attachments in database
        $db->query('UPDATE ' . TABLE_TOPICS . ' SET topic_attachments = ? WHERE topic_id = ?', [count($attachments) + count($images), $data->get('data.topic.topic_id')]);

        // Update topic
        $db->update(TABLE_TOPICS, [
            'topic_url'         => $data->get('data.topic.topic_id') . '.' . parse($post->get('topic_name')),
            'topic_text'        => $post->get('topic_text'),
            'topic_name'        => $post->get('topic_name'),
            'topic_edited'      => '1',
            'topic_edited_at'   => DATE_DATABASE
        ], $data->get('data.topic.topic_id'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Sets redirect url
        $data->set('data.redirect', '/forum/topic/' . $data->get('data.topic.topic_id') . '.' . parse($post->get('topic_name')));
    }
}