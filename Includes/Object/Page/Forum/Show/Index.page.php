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

namespace App\Page\Forum\Show;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Forum/View.phtml';

    /**
     * @var bool $notification If true - notifications will be displayed
     */
    protected bool $notification = true;

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
        if ($system->get('site_mode') == 'blog')
        {
            // Show 404 error page
            $this->error404();
        }

        // Get forum
        $row = $db->select('app.forum.get()', $this->url->getID()) or $this->error404();

        // Save forum data
        $data->set('data.forum', $row);

        // If logged user doesn't have permission to see this forum
        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.forum.permission_see')))
        {
            $this->error404();
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Forum.json');
        $breadcrumb->elm1('category')->title($data->get('data.forum.category_name'))->up()
            ->create()->jumpTo()->title($data->get('data.forum.forum_name'))->href($this->build->url->forum($data->get('data.forum')));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Panel
        $panel = new \App\Visualization\Panel\Panel('Root/Panel:/Formats/Forum.json');
        // Setup button for creating new topic
        $panel->elm1('new', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
        {    
            // If user has permission to manage topics in this forum
            if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.forum.permission_topic')))
            {
                // Logged user has permission to create topics
                if ($permission->has('topic.create'))
                {
                    // Show button
                    $panel->show();
                }
            }
        });

        // Finish panel and get ready for generate
        $data->panel = $panel->getDataToGenerate();

        // If logged user has permission to see deleted topic
        $deleted = false;
        if ($permission->has('admin.forum'))
        {
            $deleted = true;
        }

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_TOPICS);
        $pagination->url($this->url->getURL());
        $pagination->total($db->select('app.topic.parentCount()', $this->url->getID(), $deleted));
        $data->pagination = $pagination->getData();

        // List
        $list = new \App\Visualization\Lists\Lists('Root/Lists:/Formats/Topic.json');
        $list->elm1('topic')->fill(data: $db->select('app.topic.parent()', $this->url->getID(), $deleted), function: function ( \App\Visualization\Lists\Lists $list )
        {
            // Default variables
            $list
                // data.link = Link to topic
                ->set('data.link', '<a href="' . $this->build->url->topic($list->get('data')) . '">' . $list->get('data.topic_name') . '</a>')
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $list->get('data')))
                // data.date = Date of creating topic
                ->set('data.date', $this->build->date->short($list->get('data.topic_created')))
                // data.user_image = User profile image
                ->set('data.user_image', $this->build->user->image(data: $list->get('data'), role: true));
            
                // If topic contain any post
            if ($list->get('data.post_id'))
            {
                // Get from the whole data only data which is regarding to last created post
                $data = getKeysWithPrefix($list->get('data'), prefix: 'last_');

                // Set variables for last post in topic
                $list
                    // data.lastpost.user = Link to user
                    ->set('data.lastpost.user', $this->build->user->link(data: $data))
                    // data.lastpost.date = Date of creating post
                    ->set('data.lastpost.date', $this->build->date->short($data['post_created']))
                    // data.lastpost.user_image = User profile image
                    ->set('data.lastpost.user_image', $this->build->user->image(data: $data, role: true));
            }
            
            // If topic is deleted
            if ($list->get('data.deleted_id'))
            {
                // Disable row(topic)
                $list->disable();
            }
        });

        // Finish list and get ready from generate
        $data->list = $list->getDataToGenerate();

        // Set page title
        $data->set('data.head.title', $data->get('data.forum.forum_name'));

        // Set page description
        $data->set('data.head.description', $data->get('data.forum.forum_description'));
    }
}