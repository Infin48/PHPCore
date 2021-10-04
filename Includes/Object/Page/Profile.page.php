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

namespace Page;

use Block\User;
use Block\ProfilePost;
use Block\ProfilePostComment;
use Block\Admin\ProfilePost as AdminProfilePost;
use Block\Admin\ProfilePostComment as AdminProfilePostComment;

use Model\Pagination;

use Visualization\Block\Block;
use Visualization\Sidebar\Sidebar;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Profile
 */
class Profile extends Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'editor' => EDITOR_SMALL,
        'template' => '/Profile'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BLOCK
        $user = new User();
        $profilePost = new ProfilePost;
        $profilePostComment = new ProfilePostComment();

        if ($this->user->perm->has('admin.forum')) {

            $profilePost = new AdminProfilePost;
            $profilePostComment = new AdminProfilePostComment();
        }

        // USER DATA
        $profile =  $user->get($this->url->getID()) or $this->error();

        $this->data->data($profile);

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Profile');
        $this->data->breadcrumb = $breadcrumb->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_PROFILE_POSTS);
        $pagination->url($this->url->getURL());
        $pagination->total($profilePost->getParentCount($this->url->getID()));
        $profilePost->pagination = $this->data->pagination = $pagination->getData();

        if (time() - strtotime($this->data->data['user_last_activity']) > 60) {
            $online = $this->build->date->short($this->data->data['user_last_activity']);
        } else {
            $online = '<span class="online">' . strtoupper($this->language->get('L_USER_ONLINE')) . '</span>';
        }

        // SIDEBAR
        $sidebar = new Sidebar('/Profile');
        $sidebar->left();
        $sidebar->object('user')
            ->row('online')->value($online)
            ->row('registered')->value($this->build->date->short($profile['user_registered']))
            ->row('topics')->value($profile['user_topics'])
            ->row('posts')->value($profile['user_posts'])
        ->object('info')
            ->row('gender')->value($this->language->get('L_USER_GENDER_' . strtoupper($profile['user_gender'])))
            ->row('location')->value($profile['user_location'])
            ->row('age')->value($profile['user_age']);

        if (!$this->user->isLogged() or ($this->data->data['group_index'] >= LOGGED_USER_GROUP_INDEX and $this->data->data['user_id'] == LOGGED_USER_ID)) {
            $sidebar->object('user')->row('buttons')->hide();
        }


        if ($this->data->data['user_gender'] != 'undefined') {
            $sidebar->object('info')->show()->row('gender')->show();
        }

        if ($this->data->data['user_location']) {
            $sidebar->object('info')->show()->row('location')->show();
        }

        if ($this->data->data['user_age']) {
            $sidebar->object('info')->show()->row('age')->show();
        }

        $this->data->sidebar = $sidebar->getData();

        // BLOCK
        $block = new Block('/ProfilePost');
        $block->object('profilepost')->fill(data: $profilePost->getParent($this->url->getID()), function: function ( \Visualization\Block\Block $block ) use ($profilePostComment) {

            if ($this->url->is('select')) {

                if ($block->obj->get->data('profile_post_id') == $this->url->get('select')) {

                    $block->select();
                }
            }

            if ($this->user->isLogged() === false) {
                $block->delButton();
            }

            if ($block->obj->get->data('report_id') and $block->obj->get->data('report_status') == 0 and $this->user->perm->has('admin.forum')) {
                $block->notice('reported');
                $block->disable();
            }

            // IF PROFILE POST IS DELETED
            if ($block->obj->get->data('deleted_id')) {

                $block->notice('deleted');
                $block->disable();
                $block->delButton();
                $block->close();
            } else {

                if ($this->user->perm->has('profilepost.edit') === false or LOGGED_USER_ID != $block->obj->get->data('user_id')) {
                    $block->delButton('edit');
                }

                if ($this->user->perm->has('profilepost.delete') === false) {
                    $block->delButton('delete');
                }

                if ($this->user->isLogged()) {

                    if ($this->user->perm->has('profilepost.create')) {
                        $block->option('bottom')->show()->up();
                    }
                }
            }
            
            if ($block->obj->get->data('next') == 1) {
                $block->option('top')->show()->up();
            }

            $comments = $profilePostComment->getParent($block->obj->get->data('profile_post_id'));

            
            if ($this->url->is('select')) {

                if ($block->obj->get->data('profile_post_id') == ($this->url->get('select')['p'] ?? '') and !($this->url->get('select')['c'] ?? false)) {
                    $block->select();
                }

                if ($block->obj->get->data('profile_post_id') == ($this->url->get('select')['p'] ?? '') and isset($this->url->get('select')['c']) and !in_array($this->url->get('select')['c'], array_column($comments, 'profile_post_comment_id'))) {
                    $comments = array_merge($profilePostComment->getAfterNext($block->obj->get->data('profile_post_id')), $comments);
                    $block->option('top')->hide();
                }
            }
            
            $post = $block->obj->get->data();
            $block->fill(data: $comments, function: function ( \Visualization\Block\Block $block ) use ($post) {

                if ($this->user->isLogged() === false) {
                    $block->delButton();
                }

                // IF PROFILE POST OR PROFILE COMMENT IS DELETED
                if ($post['deleted_id'] ?? '' or $block->obj->get->data('deleted_id')) {

                    $block->delButton();

                    if ($block->obj->get->data('deleted_id')) {

                        $block->close();
                        $block->disable();
                        $block->notice('deleted');
                    }
                } else {

                    if ($this->user->perm->has('profilepost.edit') === false or LOGGED_USER_ID != $block->obj->get->data('user_id')) {
                        $block->delButton('edit');
                    }

                    if ($this->user->perm->has('profilepost.delete') === false) {
                        $block->delButton('delete');
                    }
                }

                if ($block->obj->get->data('report_id') and $block->obj->get->data('report_status') == 0 and $this->user->perm->has('admin.forum')) {
                    $block->notice('reported');
                    $block->disable();
                }

                
                if ($this->url->is('select')) {
                    if ($block->obj->get->data('profile_post_comment_id') == ($this->url->get('select')['c'] ?? '')) {

                        $block->select();
                        $block->up()->open()->down($block->lastInsertName());
                    }
                }
                
            });
        });
        $this->data->block = $block->getData();

        // SET HEAD OF THIS PAGE
        $this->data->head['title'] = $profile['user_name'];
    }
}