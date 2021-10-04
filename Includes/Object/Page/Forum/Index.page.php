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
        $breadcrumb = new Breadcrumb('/Forum/Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $post = new Post();
        $user = new User();
        $forum = new Forum();
        $topic = new Topic();
        $category = new Category();
        $profilePost = new ProfilePost();

        // LIST
        $list = new Lists('/Forum');

        $list->fill(data: $category->getAll(), function: function ( \Visualization\Lists\Lists $list ) use ($forum, $topic) { 

            $list->fill(data: $forum->getParent($list->obj->get->data('category_id')), function: function ( \Visualization\Lists\Lists $list ) use ($topic) { 

                // IF IS ANY LAST POST IN FORUM
                if (!empty($list->obj->get->data('topic_id'))) {
                    
                    // GET TOPIC LABELS
                    $list->obj->set->data('labels', $topic->getLabels($list->obj->get->data('topic_id')));

                    if (count($list->obj->get->data('labels')) > 2) {
                        $labels = array_slice($list->obj->get->data('labels'), 0, 3);
                        $labels[2]['label_name'] = '...';
                        $list->obj->set->data('labels', $labels);
                    }
                }
            });
        });
        $this->data->list = $list->getData();

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
            ->object('users')->row('users')->fill(data: $user->getOnline())
            ->object('profile')->fill(data: $profilePost->getLast())->getData();
    }
}