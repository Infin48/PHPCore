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
        'template' => 'Profile'
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
        $profile =  $user->get($this->getID()) or $this->error();

        $this->data->data($profile);

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Profile');
        $this->data->breadcrumb = $breadcrumb->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_PROFILE_POSTS);
        $pagination->url($this->getURL());
        $pagination->total($profilePost->getParentCount($this->getID()));
        $profilePost->pagination = $this->data->pagination = $pagination->getData();

        // BLOCK
        $block = new Block('ProfilePost');
        
        foreach ($profilePost->getParent($this->getID()) as $item) {
            
            $block->object('profilepost')->appTo($item)->jumpTo();
            
            if ($this->url->is('select')) {

                if ($item['profile_post_id'] == $this->url->get('select')) {

                    $block->select();
                }
            }

            if ($this->user->isLogged() === false) {
                $block->delButton();
            }

            if ($item['report_id'] and $item['report_status'] == 0 and $this->user->perm->has('admin.forum')) {
                $block->notice('reported');
                $block->disable();
            }

            // IF PROFILE POST IS DELETED
            if ($item['deleted_id']) {

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

                if ($this->user->isLogged()) {

                    if ($this->user->perm->has('profilepost.create')) {
                        $block->option('bottom')->show();
                    }
                }
            }
            
            if ($item['next'] == 1 ) {
                $block->option('top')->show();
            }

            foreach ($profilePostComment->getParent($item['profile_post_id']) as $_item) {

                $block->appTo($_item)->jumpTo();

                if ($this->user->isLogged() === false) {
                    $block->delButton();
                }

                // IF PROFILE POST OR PROFILE COMMENT IS DELETED
                if ($item['deleted_id'] or $_item['deleted_id']) {

                    $block->delButton();

                    if ($_item['deleted_id']) {

                        $block->close();
                        $block->disable();
                        $block->notice('deleted');
                    }
                } else {

                    if ($this->user->perm->has('profilepost.edit') === false) {
                        $block->delButton('edit');
                    }

                    if ($this->user->perm->has('profilepost.delete') === false) {
                        $block->delButton('delete');
                    }
                }

                if ($_item['report_id'] and $_item['report_status'] == 0 and $this->user->perm->has('admin.forum')) {
                    $block->notice('reported');
                    $block->disable();
                }

                if ($this->url->is('select')) {

                    if ('c' . $_item['profile_post_comment_id'] == $this->url->get('select')) {

                        $block->select();
                        $block->up()->open();
                    }
                }
            }
        }
        $this->data->block = $block->getData();

        // SET HEAD OF THIS PAGE
        $this->data->head['title'] = $profile['user_name'];
    }
}