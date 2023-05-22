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
 * Index
 */
class Index extends Page
{
    /**
     * @var bool $header If true - big header will be showed
     */
    protected bool $header = true;

    /**
     * @var bool $notification If true - notifications will be displayed
     */
    protected bool $notification = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Index.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Index.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // File model
        $file = new \App\Model\File\File();

        // If is neabled blog mode
        if ($system->get('site.mode') == 'blog')
		{
            // If user is logged
            if ($user->isLogged())
            {
                // If user has permissin to create new articles
                if ($permission->has('article.create'))
                {
                    // Panel
                    $panel = new \App\Visualization\Panel\Panel('Root/Panel:/Formats/Index.json');
                    $data->panel = $panel->getDataToGenerate();
                }
            }

            // Pagination
            $pagination = new \App\Model\Pagination();
            $pagination->max(MAX_NEWS);
            $pagination->url($this->url->getURL());
            $pagination->total($db->select('app.article.count()'));
            $data->pagination = $pagination->getData();

            // Setup articles
            $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Index.json');
            $block->elm1('new')->fill(data: $db->select('app.article.all()'), function: function ( \App\Visualization\Block\Block $block ) use ($system)
            {
                // Define variables
                $block
                    // data.link = Link to article
                    ->set('data.link', '<a href="' . $this->build->url->article( data: $block->get('data') ) . '">' . $block->get('data.article_name'). '</a>')
                    // data.date = Date of created article
                    ->set('data.date', $this->build->date->short($block->get('data.article_created'), true))
                    // data.text = Text of article
                    ->set('data.text', truncate($block->get('data.article_text'), 400))
                    // data.views = Number of views
                    ->set('data.views', $block->get('data.article_views'));

                // If profiles are enabled
                if ($system->get('site.mode.blog.profiles'))
                {
                    $block
                        // data.user = Link to user
                        ->set('data.user', $this->build->user->link(data: $block->get('data')))
                        // data.group = Group of user
                        ->set('data.group', $this->build->user->group(data: $block->get('data')))
                        // date.user_image = User's profile image
                        ->set('data.user_image', $this->build->user->image(data: $block->get('data'), online: true, role: true, size: '40x40'));
                }

                // If article has header image
                if ($block->get('data.article_image'))
                {
                    // Set path to image
                    $block->set('data.image_url', '/Uploads/Articles/' . $block->get('data.article_id') . '/Header.' . $block->get('data.article_image'));
                }
            });

            // Finish block and ret ready for generate
            $data->block = $block->getDataToGenerate();

            return;
		}

        // If logged user has permission to see deleted topic
        $deleted = false;
        if ($permission->has('admin.forum')) 
        {
            $deleted = true;
        }

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_NEWS);
        $pagination->url($this->url->getURL());
        $pagination->total($db->select('app.news.count()', $deleted));
        $data->pagination = $pagination->getData();

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Index.json');

        // Setup topics
        $block->elm1('new')->fill(data: $db->select('app.news.all()', $deleted), function: function ( \App\Visualization\Block\Block $block )
        {
            // If topic is deleted
            if ($block->get('data.deleted_id'))
            {
                // Disable this row
                $block->disable();
            }
            // Define values to template
            $block
                // data.link = Link to topic
                ->set('data.link', '<a href="' . $this->build->url->topic( data: $block->get('data') ) . '">' . $block->get('data.topic_name') . '</a>')
                // data.date = Date of created topic
                ->set('data.date', $this->build->date->short($block->get('data.topic_created'), true))
                // data.text = Text of topic
                ->set('data.text', truncate($block->get('data.topic_text'), 400))
                // data.views = Number of views
                ->set('data.views', $block->get('data.topic_views'))
                // data.posts = Number of posts
                ->set('data.posts', $block->get('data.topic_posts'))
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $block->get('data')))
                // data.group = Group of user
                ->set('data.group', $this->build->user->group(data: $block->get('data')))
                // date.user_image = User's profile image
                ->set('data.user_image', $this->build->user->image(data: $block->get('data'), online: true, role: true, size: '40x40'));

            // If topic has header image
            if ($block->get('data.topic_image'))
            {
                // Set path to image
                $block->set('data.image_url', '/Uploads/Topics/' . $block->get('data.topic_id') . '/Header.' . $block->get('data.topic_image'));
            }

            // If topic is sticked
            if ($block->get('data.topic_sticked'))
            {
                // Select this row
                $block->select();
            }
        });

        // Finish block and get ready for generate
        $data->block = $block->getDataToGenerate();

        // Forum stats
        $stats = $db->select('app.forum.stats()');

        // List of online users
        $onlineUsers = $db->select('app.user.online()');

        // Sidebar
        $sidebar = new \App\Visualization\Sidebar\Sidebar('Root/Sidebar:/Formats/Basic.json');

        $sidebar
            // Setup last posts
            ->elm1('posts')->fill(data: $db->select('app.post.last()', 5, $deleted), function: function ( \App\Visualization\Sidebar\Sidebar $sidebar )
            {
                // Define variables
                $sidebar
                    // data.user = Link to user
                    ->set('data.user', $this->build->user->link(data: $sidebar->get('data')))
                    // data.date = Date of created post or topic
                    ->set('data.date', $this->build->date->short($sidebar->get('data.created'), true))
                    // date.user_image = User's profile image
                    ->set('data.user_image', $this->build->user->image(data: $sidebar->get('data'), role: true))
                    // data.name = Name of topic
                    ->set('data.name', truncate($sidebar->get('data.topic_name'), 30));
            
                // Set default href to topic
                $href = $this->build->url->topic($sidebar->get('data'));
                // But if exists post_id
                // Means that it is created newest post
                if ($sidebar->get('data.post_id'))
                {
                    // So change href to this post
                    $href = $this->build->url->post($sidebar->get('data'));
                }
                // Build the link
                $sidebar->set('data.link', '<a href="' . $href . '">' . $sidebar->get('data.topic_name') . '</a>');

                // If this post or topic is deleted
                if ($sidebar->get('data.deleted_id'))
                {
                    // Disable this row
                    $sidebar->disable();
                }

                // If topic has more than two labels
                if (count($sidebar->get('data.labels')) > 2)
                {
                    // Shorten list of labels to three
                    $labels = array_slice($sidebar->get('data.labels'), 0, 3);
                    $labels[2]['label_name'] = '...';
                    $sidebar->set('data.labels', $labels);
                }
            })

            // Setup profile posts
            ->elm1('profileposts')->fill(data: $db->select('app.profile-post.last()', 5, $deleted), function: function ( \App\Visualization\Sidebar\Sidebar $sidebar )
            {
                // Define variables
                $sidebar
                    // data.date = Date of creating profile post
                    ->set('data.date', $this->build->date->short($sidebar->get('data.profile_post_created')))
                    // data.text = Text of profile post
                    ->set('data.text', truncate($sidebar->get('data.profile_post_text'), 100))
                    // data.user = Link to user
                    ->set('data.user', $this->build->user->link(data: $sidebar->get('data'), href: $this->build->url->profilepost($sidebar->get('data'))))
                    // date.user_image = User's profile image
                    ->set('data.user_image', $this->build->user->image(data: $sidebar->get('data'), role: true));

                // If user didn't created profile post on own profile
                if ((int)$sidebar->get('data.profile_id') !== (int)$sidebar->get('data.user_id'))
                {
                    // Show name of user where was profile psot created
                    $data = getKeysWithPrefix($sidebar->get('data'), prefix: 'two_');
                    $sidebar->set('data.user_second', '<i class="fa-solid fa-caret-right"></i> ' . $this->build->user->link(data: $data, href: $this->build->url->profilepost($sidebar->get('data'))));
                }

                // If profile post is deleted
                if ($sidebar->get('data.deleted_id'))
                {
                    // Disable this row
                    $sidebar->disable();
                }
            })
            // Setup stats
            ->elm1('stats')->elm2('table')
                // Insert numer of created topics in the whole forum 
                ->elm3('topics')->value($stats['topic'])
                // Insert numer of created posts in the whole forum
                ->elm3('posts')->value($stats['post'])
                // Insert numer of registered users in the whole website
                ->elm3('users')->value($stats['user'])
            // Setup online users
            ->elm1('onlineusers')
                // Inset number or online users
                ->elm2('bottom')->set('data.count', count($onlineUsers))
                // Insert online users to sidebar
                ->elm2('users')->fill(data: $onlineUsers, function: function ( \App\Visualization\Sidebar\Sidebar $sidebar )
                {
                    // Define user profile image
                    $sidebar->set('data.user_image', $this->build->user->linkImg($sidebar->get('data'), size: '25x25', role: true));
                });

        // Save sidebar and get ready to generate
        $data->sidebar = $sidebar->getDataToGenerate();
    }
}