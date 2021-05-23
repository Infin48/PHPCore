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
        'template' => 'Index',
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
        $breadcrumb = new Breadcrumb('Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_NEWS);
        $pagination->url($this->getURL());
        $pagination->total($news->getAllCount());
        $news->pagination = $this->data->pagination = $pagination->getData();

        // BLOCK
        $block = new Block('Index');
      
        foreach ($news->getAll() as $item) {

            $item['labels'] = $topic->getLabels($item['topic_id']);

            $item['topic_text'] = truncate($item['topic_text'], 400);

            if ($item['topic_image']) {
                $item['image_url'] = '/Uploads/Topic/' . $item['topic_id'] . '.' . $item['topic_image'];
            }

            $block->object('new')->appTo($item)->jumpTo();

            if ($item['deleted_id']) {
                $block->disable();
            }

            if ($item['is_sticky']) {
                $block->select();
            }
        }

        $this->data->block = $block->getData();

        // SIDEBAR
        $sidebar = new Sidebar('Basic');

        foreach ($post->getLast() as $item) {

            $item['labels'] = $topic->getLabels($item['topic_id']);

            if (count($item['labels']) > 2) {
                $item['labels'] = array_slice($item['labels'], 0, 3);
                $item['labels'][2]['label_name'] = '...';
            }

            $sidebar->object('posts')->appTo($item);
        }

        // FORUM STATS
        $stats = $forum->getStats();

        $this->data->sidebar = $sidebar->object('stats')
            ->row('topics')->value($stats['topic'])
            ->row('posts')->value($stats['post'])
            ->row('users')->value($stats['user'])
            ->object('users')->fill($user->getOnline())
            ->object('profile')->fill($profilePost->getLast())->getData();
    }
}