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

namespace App\Page;

/**
 * Label
 */
class Label extends Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Label.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // If label ID is not set in URL
        if (!$this->url->getID())
        {
            // Show error page
            $this->error404();
        }

        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // File model
        $file = new \App\Model\File\File();

        // Get data about label from database
        $row = $db->select('app.label.get()', $this->url->getID()) or $this->error404();

        // Save label data
        $data->set('data.label', $row);

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Index.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.label.label_name'))->href('/label/' . $this->url->getID() . '.' . $data->get('data.label.label_class') . '/');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Set page title
        $data->set('data.head.title', $data->get('data.label.label_name'));

        // If logged user has permission to see deleted topic
        $deleted = false;
        if ($permission->has('admin.forum')) 
        {
            $deleted = true;
        }

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(20);
        $pagination->url($this->url->getURL());

        // If blog mode is enabled
        if ($system->get('site_mode') == 'blog')
        {
            // Setup pagination
            $pagination->total($db->select('app.article.labelCount()', $this->url->getID()));
            $data->pagination = $pagination->getData();

            // Block
            $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Index.json');

            // Fill block with articles
            $block->elm1('new')->fill(data: $db->select('app.article.label()', $this->url->getID()), function: function ( $block ) use ($file, $system)
            {
                // Define variables
                $block
                    // data.link - Link to article
                    ->set('data.link', '<a href="' . $this->build->url->article( data: $block->get('data')) . '">' . $block->get('data.article_name') . '</a>')
                    // data.name - Name of article
                    ->set('data.name', truncate($block->get('data.article_name')))
                    // data.text - Text of article
                    ->set('data.text', truncate($block->get('data.article_text'), 400))
                    // data.views - Number of views
                    ->set('data.views', $block->get('data.article_views'));

                // If profiles are enabled
                if ($system->get('site_mode_blog_profiles'))
                {
                    $block
                        // data.user = Link to user
                        ->set('data.user', $this->build->user->link(data: $block->get('data')))
                        // data.group = Group of user
                        ->set('data.group', $this->build->user->group(data: $block->get('data')))
                        // date.user_image = User's profile image
                        ->set('data.user_image', $this->build->user->image(data: $block->get('data'), online: true, role: true, size: '40x40'));
                }

                // Search header image
                $file->getFiles(
                    path: '/Uploads/Articles/' . $block->get('data.article_id') . '/Header.*',
                    function: function ( \App\Model\File\File $file, string $path ) use ($block)
                    {
                        $block->set('data.image_url', str_replace(ROOT, '', $path) . '?' . strtotime($block->get('data.article_edited')));
                    }
                );
            });

            // Save block and get ready to generate
            $data->block = $block->getDataToGenerate();

            return;
        }

        // Setup pagination
        $pagination->total($db->select('app.topic.labelCount()', $this->url->getID(), $deleted));
        $data->pagination = $pagination->getData();

        // List
        $list = new \App\Visualization\Lists\Lists('Root/Lists:/Formats/Topic.json');

        // Fill list with topics
        $list->elm1('topic')->fill(data: $db->select('app.topic.label()', $this->url->getID(), $deleted), function: function ( \App\Visualization\Lists\Lists $list )
        {
            // Default variables
            $list
                // data.link = Link to topic
                ->set('data.link', '<a href="' . $this->build->url->topic($list->get('data')) . '">' . $list->get('data.topic_name') . '</a>')
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $list->get('data')))
                // data.date = Date of creating topic
                ->set('data.date', $this->build->date->short($list->get('data.topic_created')))
                // data.user_image = User profile image
                ->set('data.user_image', $this->build->user->image(data: $list->get('data'), role: true));


                // If topic contain any post
            if ($list->get('data.post_id'))
            {
                // Get from the whole data only data which is regarding to last created post
                $data = getKeysWithPrefix($list->get('data'), prefix: 'last_');

                // Set variables for last post in topic
                $list
                    // data.lastpost.user = Link to user
                    ->set('data.lastpost.user', $this->build->user->link(data: $data))
                    // data.lastpost.date = Date of creating post
                    ->set('data.lastpost.date', $this->build->date->short($data['post_created']))
                    // data.lastpost.user_image = User profile image
                    ->set('data.lastpost.user_image', $this->build->user->image(data: $data, role: true));
            }
            
            // If topic is deleted
            if ($list->get('data.deleted_id'))
            {
                // Disable row(topic)
                $list->disable();
            }
        });

        // Finish list and get ready from generate
        $data->list = $list->getDataToGenerate();
    }
}