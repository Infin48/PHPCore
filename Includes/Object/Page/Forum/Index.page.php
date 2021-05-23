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

namespace Page\Forum;

use Block\Post;
use Block\User;
use Block\Forum;
use Block\Topic;
use Block\Category;
use Block\ProfilePost;

use Visualization\Lists\Lists;
use Visualization\Sidebar\Sidebar;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Index
 */
class Index extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'header' => true,
        'template' => 'Forum/Index',
        'notification' => true
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Forum/Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $post = new Post();
        $user = new User();
        $forum = new Forum();
        $topic = new Topic();
        $category = new Category();
        $profilePost = new ProfilePost();

        // LIST
        $list = new Lists('Forum');

        foreach ($category->getAll() as $item) {

            $list->sync()->appTo($item)->jumpTo();

            foreach ($forum->getParent($item['category_id']) as $_item) {

                // IF IS ANY LAST POST IN FORUM
                if (!empty($_item['topic_id'])) {
                    
                    // GET TOPIC LABELS
                    $_item['labels'] = $topic->getLabels($_item['topic_id']);

                    if (count($_item['labels']) > 2) {
                        $_item['labels'] = array_slice($_item['labels'], 0, 3);
                        $_item['labels'][2]['label_name'] = '...';
                    }
                }

                // SET FORUMS TO CATEGORY
                $list->appTo($_item);
            }
        }
        $this->data->list = $list->getData();

        // SIDEBAR        
        $sidebar = new Sidebar('Basic');
        $sidebar->object('posts');

        foreach ($post->getLast() as $item) {

            // GET TOPIC LABELS
            $item['labels'] = $topic->getLabels($item['topic_id']);

            if (count($item['labels']) > 2) {
                $item['labels'] = array_slice($item['labels'], 0, 3);
                $item['labels'][2]['label_name'] = '...';
            }

            $sidebar->appTo($item);
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