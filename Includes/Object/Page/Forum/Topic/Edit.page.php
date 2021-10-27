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

namespace Page\Forum\Topic;

use Block\Topic as BlockTopic;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Edit
 */
class Edit extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'editor' => EDITOR_BIG,
        'template' => '/Forum/Topic/Edit',
        'permission' => 'topic.edit'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BLOCKS
        $topic = new BlockTopic();

        // TOPIC
        $topic = $topic->get($this->url->getID()) or $this->error();

        // TOPIC IS NOT MINE
        $topic['user_id'] == LOGGED_USER_ID or $this->error();

        // IF USER DOESN'T HAVE PERMISSION TO CRAETE TOPIC
        $topic['topic_permission'] == 1 or $this->error();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Forum/Topic');
        $breadcrumb->object('category')->title('$' . $topic['category_name']);
        $breadcrumb->object('forum')->title('$' . $topic['forum_name']);
        $breadcrumb->object('forum')->href($this->build->url->forum($topic));
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/Topic');
        $field->data($topic);

        if ($this->user->perm->has('topic.image')) {
            $field->object('topic')->row('topic_image')->show();
        }

        $this->data->field = $field->getData();

        // EDIT TOPIC
        $this->process->form(type: '/Topic/Edit', data: [
            'topic_id'      => $topic['topic_id']
        ]);

        // HEAD
        $this->data->head['title'] = $topic['topic_name'];
        $this->data->head['description'] = $topic['topic_text'];
    }
}