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
 * Post
 */
class Post extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // Form
        $post = new \App\Model\Post;

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Language
        $language = $data->get('inst.language');

        // If logged user has permission to see deleted content
        $deleted = false;
        if ($permission->has('admin.forum'))
        {
            $deleted = true;
        }

        $id = $post->get('id');
        if (!$id)
        {
            $id = $this->url->get('id');
        }

        // Block
        $row = $db->select('app.post.get()', $id, $deleted) or $this->error404();

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Topic.json');
        $block->elm1('post')->appTo(data: $row, function: function ( \App\Visualization\Block\Block $block ) use ($user, $permission, $language)
        {
            // Default variables
            $block
                // data.html.ajax-id = ID for ajax requests
                ->set('data.html.ajax-id', $block->get('data.post_id'))
                // data.text = Text of post
                ->set('data.text', $block->get('data.post_text'))
                // data.group = User's group
                ->set('data.group', $this->build->user->group(data: $block->get('data')))
                // data.date = Date of creating post
                ->set('data.date', $this->build->date->long($block->get('data.post_created'), true))
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $block->get('data')))
                // data.user_image = User's profile image
                ->set('data.user_image', $this->build->user->image(data: $block->get('data'), role: true, online: true, size: '50x50'))
                // data.name = Name of topic with Re
                ->set('data.name', $language->get('L_RE') . ': ' . $block->get('data.topic_name'))
                // data.edited = Date of last editing post
                ->set('data.edited', $this->build->date->long($block->get('data.post_edited_at')));

            // But if this topic wasn't edited yet
            if ($block->get('data.post_edited') == 0)
            {
                // Erase it
                $block->set('data.edited', '');
            }

            // If user has any reputation
            if ($block->get('data.user_reputation'))
            {
                // Show reputation
                $block->set('data.reputation', $this->build->user->reputation($block->get('data.user_reputation')));
            }

            // Foreach every like on topic
            foreach ($block->get('data.likes') as $key => $like)
            {
                // If like is from logged user
                if (LOGGED_USER_ID == $like['user_id'])
                {
                    // Show "you" instead of username 
                    $block->set('data.likes.' . $key . '.user_name', $language->get('L_YOU'));
                    continue;
                }
                // Build link to user
                $block->set('data.likes.' . $key . '.user_name', $this->build->user->link(data: $like, group: false));
            }

            // If topic is not deleted
            if (!$block->get('data.deleted_id_topic'))
            {
                // If post is not deleted
                if (!$block->get('data.deleted_id'))
                {
                    // Topic is not locked
                    if ($block->get('data.topic_locked') == 0)
                    {
                        // Logged user has permission to manage posts in this forum
                        if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $block->get('data.permission_post')))
                        {
                            // Logged user has permission to delete posts
                            if ($permission->has('post.delete'))
                            {
                                $block->show('data.button.delete');
                            }

                            // Logged user has permission to create posts
                            if ($permission->has('post.create'))
                            {
                                $block->show('data.button.quote');
                            }

                            // Logged user has permission to edit own posts
                            if ($permission->has('post.edit'))
                            {
                                // This post is not from logged user
                                if (LOGGED_USER_ID == $block->get('data.user_id'))
                                {
                                    // Show 'edit' button
                                    $block->show('data.button.edit');
                                }
                            }
                        }
                    }

                    // User is logged
                    if ($user->isLogged())
                    {
                        // Show 'report' button
                        $block->show('data.button.report');

                        // Post is not from logged user
                        if (LOGGED_USER_ID != $block->get('data.user_id'))
                        {
                            // User already liked this post
                            if (in_array(LOGGED_USER_ID, array_column($block->get('data.likes') ?: [], 'user_id'))) {
                                $block->show('data.button.unlike');
                            } else {
                                $block->show('data.button.like');
                            }
                        }
                    }
                }
            }

            // If post is deleted
            if ($block->get('data.deleted_id'))
            {
                // Show delete dnotice
                $block->notice('deleted');

                // Disable block
                $block->disable();

                // Close block
                $block->close();
            }

            // Post is reported
            if ($block->get('data.report_id'))
            {
                // Report is not closed
                if ($block->get('data.report_status') == 0)
                {
                    // Logged user has permisison to see reported content
                    if ($permission->has('admin.forum'))
                    {
                        // Show notice
                        $block->notice('reported');
                    }
                }
            }

            // If post is selected
            if ($this->url->get('selected'))
            {
                // select this row(post)
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