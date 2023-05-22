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
 * Comment
 */
class Comment extends \App\Page\Page
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

        $row = $db->select('app.profile-post-comment.get()', $id, $deleted) or $this->error404();

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Profile/ProfilePostComment.json');
        $block->elm1('profilepostcomment')->appTo(data: $row, function: function ( \App\Visualization\Block\Block $block ) use ($permission, $user, $post)
        {
            $block->set('data.html.ajax-id', $block->get('data.profile_post_comment_id'));
            $block->set('data.text', $block->get('data.profile_post_comment_text'));
            $block->set('data.date', $this->build->date->short($block->get('data.profile_post_comment_created')));
            $block->set('data.user', $this->build->user->link(data: $block->get('data')));
            $block->set('data.user_image', $this->build->user->image(data: $block->get('data'), role: true, online: true, size: '25x25'));

            // If profile comment is not deleted
            if (!$block->get('data.deleted_id'))
            {
                // If profile post is not deleted
                if (!$block->get('data.deleted_id_profile_post'))
                {
                    // Logged user has permission to delete profile posts
                    if ($permission->has('profilepost.delete'))
                    {
                        $block->show('data.button.delete');
                    }

                    // Logged user has permission to edit own profile posts
                    if ($permission->has('profilepost.edit'))
                    {
                        // This post is not from logged user
                        if (LOGGED_USER_ID == $block->get('data.user_id'))
                        {
                            // Show 'edit' button
                            $block->show('data.button.edit');
                        }
                    }

                    // User is logged
                    if ($user->isLogged())
                    {
                        // Show 'report' button
                        $block->show('data.button.report');
                    }
                }
            }

            // If profile comment is deleted
            if ($block->get('data.deleted_id'))
            {
                // Show notice
                $block->notice('deleted');

                // Disable block
                $block->disable();

                // Close block
                $block->close();
            }

            // Profile comment is reported
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

                        // Disable post
                        $block->disable();
                    }
                }
            }

            // If profile comment is selected
            if ($post->get('selected'))
            {
                $block->select();
            }
        });

        $data->block = $block->getDataToGenerate();
        
        $this->data = $data;

        $this->path = new \App\Model\Path();
        $this->language = $data->get('inst.language');

        require $this->path->build('Root/Style:/Templates/Blocks/Visualization/Block/Block.phtml');

        exit();
    }
}