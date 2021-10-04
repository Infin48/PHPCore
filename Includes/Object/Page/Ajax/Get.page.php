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

namespace Page\Ajax;

use Block\Post;
use Block\User;
use Block\Topic;
use Block\ProfilePostComment;
use Block\Admin\ProfilePostComment as AdminProfilePostComment;

use Model\Ajax;

use Visualization\Block\Block;

/**
 * Get
 */
class Get extends \Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $ajax = new Ajax();

        $ajax->ajax(

            require: ['process'],

            exec: function ( \Model\Ajax $ajax ) {

                switch ($ajax->get('process')) {

                    case '/ProfilePostComment/Previous':

                        $ajax->get('id') or exit();

                        $profilePostComment = new ProfilePostComment();
                        if ($this->user->perm->has('admin.forum')) {
                            $profilePostComment = new AdminProfilePostComment();
                        }

                        $data = $profilePostComment->getAfterNext($ajax->get('id'));
                        if ($data) {
                            $ajax->ok();
                        }

                        $block = new Block('/ProfilePostComment');
                        $block->object('profilepostcomment')->fill(data: $data, function: function ( \Visualization\Block\Block $block) {

                            if ($this->user->isLogged() === false or $block->obj->get->data('profile_post_deleted_id') != null) {
                                $block->delButton();
                            }

                            if ($block->obj->get->data('report_id') and $block->obj->get->data('report_status') == 0 and $this->user->perm->has('admin.forum')) {
                                $block->notice('reported');
                                $block->disable();
                            }

                            if ($block->obj->get->data('deleted_id')) {

                                $block->notice('deleted');
                                $block->disable();
                                $block->delButton();
                                $block->close();
                            } else {

                                if ($this->user->perm->has('profilepost.edit') === false) {
                                    $block->delButton('edit');
                                }

                                if ($this->user->perm->has('profilepost.delete') === false) {
                                    $block->delButton('delete');
                                }
                            }
                        });

                        $blocks = '';
                        foreach ($block->getData()['body']['profilepostcomment']['body'] as $row) {
                            $blocks .= $this->file('/Blocks/Visualization/Block/ProfilePostComment.phtml', [
                                'variable' => '$row',
                                'data' => $row
                            ]);
                        }

                        $ajax->data([
                            'content' => $blocks
                        ]);

                    break;

                    case '/Post/Likes':
                    case '/Topic/Likes':

                        $block = match ($ajax->get('process')) {
                            '/Post/Likes' => new Post(),
                            '/Topic/Likes' => new Topic()
                        };
                        $content = '';
                        foreach ($block->getLikesAll($ajax->get('id')) as $like) {
                            $content .= $this->build->user->info($like);
                        }
                
                        if ($content) {
                            $ajax->ok();
                        }
                
                        $ajax->data([
                            'windowTitle' => $this->language->get('L_LIKE_LIST'),
                            'windowContent' => $content
                        ]);

                    break;

                    case '/User':

                        $ajax->get('user') or exit();
                    
                        $user = new User();
                        if ($data = $user->getByName($ajax->get('user'))) {
                            if ($data['user_id'] != LOGGED_USER_ID) {
                                
                                $ajax->data([
                                    'user' => $this->build->user->linkImg($data),
                                    'user_id' => $data['user_id']
                                ]);
                                $ajax->ok();
                            }
                        }
                    
                    break;
                }
            }
        );
        $ajax->end();
    }
}