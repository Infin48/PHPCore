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

namespace App\Page\Forum;

/**
 * Index
 */
class Index extends \App\Page\Page
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
    protected string $template = 'Root/Style:/Templates/Forum/Index.phtml';

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

        // If is enabled blog mode
        if ($system->get('site.mode') == 'blog')
        {
            // Show 404 error page
            $this->error404();
        }
		
        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Forum.json');
        $breadcrumb->delete('body.category');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();
        
        // If logged user has permission to see deleted content
        $deleted = false;
        if ($permission->has('admin.forum'))
        {
            $deleted = true;
        }

        // List
        $list = new \App\Visualization\Lists\Lists('Root/Lists:/Formats/Forum.json');

        // Setup categories
        $list->fill(data: $db->select('app.category.all()'), function: function ( \App\Visualization\Lists\Lists $list ) use ($db, $deleted)
        {
            // If logged user or visitor don!t have permission
            // to see this category
            if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $list->get('data.permission_see')))
            {
                // Remove this row(category)
                return false;
            }

            // Setup forums
            $list->fill(data: $db->select('app.forum.parent()', $list->get('data.category_id'), $deleted), function: function ( \App\Visualization\Lists\Lists $list ) use (&$main)
            {
                // If logged user or visitor don!t have permission
                // to see this forum
                if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $list->get('data.permission_see')))
                {
                    // Remove this row(forum)
                    return false;
                }

                // Set default href to forum
                $href = $this->url->build('/forum/show/' . $list->get('data.forum_id') . '.' . parse($list->get('data.forum_name')));
                // But if forum has set custom link
                if ($list->get('data.forum_link'))
                {
                    // Change href to this link
                    $href = $list->get('data.forum_link');

                    $list
                        // And remove columns with information about forum
                        ->delete('options.template.small')
                        // And column with last post
                        ->delete('options.template.medium');
                }
                // Set href
                $list->set('data.href', $href);

                // If forum contains any last post
                if ($list->get('data.topic_id'))
                {
                    // Define values
                    // data.lastpost.link = Link to topic or post
                    // data.lastpost.date = Date of created last post or topic
                    // data.lastpost.user = Link to user
                    // date.lastpost.user_image = User's profile image
                    $list->set('data.lastpost.link', '<a href="' . $this->build->url->topic($list->get('data')). '">' . truncate($list->get('data.topic_name'), 25) . '</a>');
                    $list->set('data.lastpost.date', $this->build->date->short($list->get('data.created'), true));
                    $list->set('data.lastpost.user', $this->build->user->link(data: $list->get('data')));
                    $list->set('data.lastpost.user_image', $this->build->user->image(data: $list->get('data'), role: true));
                }

                // If topic from last post has any labels
                if (!empty($list->get('data.labels')))
                {    
                    // If number of labels is more than two
                    if (count($list->get('data.labels')) > 2)
                    {
                        // Shorten labels to three
                        $labels = array_slice($list->get('data.labels'), 0, 3);
                        $labels[2]['label_name'] = '...';
                        $list->set('data.labels', $labels);
                    }
                }
            });
        });

        // Finish list and get ready to generate
        $data->list = $list->getDataToGenerate();

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