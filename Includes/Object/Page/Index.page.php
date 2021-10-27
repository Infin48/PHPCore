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

use Block\Post;
use Block\News;
use Block\User;
use Block\Topic;
use Block\Forum;
use Block\ProfilePost;
use Block\Admin\News as AdminNews;

use Model\Pagination;

use Visualization\Block\Block;
use Visualization\Sidebar\Sidebar;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Index
 */
class Index extends Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'header' => true,
        'template' => '/Index',
        'notification' => true
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
        $post = new Post();
        $news = new News();
        $topic = new Topic();
        $forum = new Forum();
        $profilePost = new ProfilePost();

        if ($this->user->perm->has('admin.forum')) {
            $news = new AdminNews();
        }

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_NEWS);
        $pagination->url($this->url->getURL());
        $pagination->total($news->getAllCount());
        $news->pagination = $this->data->pagination = $pagination->getData();

        // BLOCK
        $block = new Block('/Index');
        $block->object('new')->fill(data: $news->getAll(), function: function ( \Visualization\Block\Block $block ) use ($topic) {
            
            $block->obj->set->data('labels', $topic->getLabels($block->obj->get->data('topic_id')));
            $block->obj->set->data('text', truncate($block->obj->get->data('topic_text'), 400));

            if ($block->obj->get->data('topic_image')) {
                $block->obj->set->data('image_url', '/Uploads/Topic/' . $block->obj->get->data('topic_id') . '.' . $block->obj->get->data('topic_image'));
            }

            if ($block->obj->get->data('deleted_id')) {
                $block->disable();
            }

            if ($block->obj->get->data('topic_sticked')) {
                $block->select();
            }
        });
        $this->data->block = $block->getData();

        // SIDEBAR
        $sidebar = new Sidebar('/Basic');
        $sidebar->object('posts')->fill(data: $post->getLast(), function: function ( \Visualization\Sidebar\Sidebar $sidebar ) use ($topic) {

            $sidebar->obj->set->data('labels', $topic->getLabels($sidebar->obj->get->data('topic_id')));

            if (count($sidebar->obj->get->data('labels')) > 2) {
                $labels = array_slice($sidebar->obj->get->data('labels'), 0, 3);
                $labels[2]['label_name'] = '...';
                $sidebar->obj->set->data('labels', $labels);
            }
        });

        // FORUM STATS
        $stats = $forum->getStats();

        $this->data->sidebar = $sidebar->object('stats')->row('table')
            ->option('topics')->value($stats['topic'])
            ->option('posts')->value($stats['post'])
            ->option('users')->value($stats['user'])
            ->object('onlineusers')->row('users')->fill(data: $user->getOnline())
            ->object('profileposts')->fill(data: $profilePost->getLast())->getData();
    }
}