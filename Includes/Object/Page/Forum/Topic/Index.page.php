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

use Block\Post;
use Block\Label;
use Block\Topic as BlockTopic;
use Block\Forum;
use Block\Admin\Post as AdminPost;
use Block\Admin\Topic as AdminTopic;

use Model\Pagination;
use Model\Database\Query;

use Visualization\Block\Block;
use Visualization\Panel\Panel;
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
        'id' => int,
        'editor' => EDITOR_BIG,
        'template' => 'Forum/Topic/View',
        'notification' => true
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BLOCKS
        $forum = new Forum();
        $_topic = new BlockTopic();
        $post = new Post();
        $label = new Label();
        $query = new Query();

        if ($this->user->perm->has('admin.forum')) {
            $post = new AdminPost();
            $_topic = new AdminTopic();
        }

        // GET TOPIC DATA
        $topic = $_topic->get($this->url->getID()) or $this->error();

        // GET TOPIC LABELS
        $topic['labels'] = $_topic->getLabels($this->url->getID());

        // GET TOPIC LIKES
        $topic['likes'] = $_topic->getLikes($this->url->getID());

        $this->data->data([
            'labels' => $topic['labels'],
            'forum_id' => $topic['forum_id'],
            'topic_id' => $topic['topic_id'],
            'report_id' => $topic['report_id'],
            'topic_locked' => $topic['topic_locked'],
            'topic_name' => $topic['topic_name'],
            'deleted_id' => $topic['deleted_id'],
            'topic_image' => $topic['topic_image'],
            'report_status' => $topic['report_status'] ?? 0,
        ]);

        if ($topic['topic_image']) {
            $topic['image_url'] = '/Uploads/Topic/' . $topic['topic_id'] . '.' . $topic['topic_image'];
        }

        // HEAD
        $this->data->head['title'] = $topic['topic_name'];
        $this->data->head['description'] = $topic['topic_text'];

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Forum/Topic');
        $breadcrumb->object('category')->title('$' . $topic['category_name']);
        $breadcrumb->object('forum')->title('$' . $topic['forum_name']);
        $breadcrumb->object('forum')->href($this->build->url->forum($topic));
        $this->data->breadcrumb = $breadcrumb->getData();
        
        // TOPIC IS NOT FROM LOGGED USER
        if ($topic['user_id'] != LOGGED_USER_ID) {
            $this->user->perm->disable('topic.edit');
        }

        // TOPIC IS LOCKED
        if ($topic['topic_locked'] == 1) {
            $this->user->perm->disable('post.*');
            $this->user->perm->disable('topic.edit');
            $this->user->perm->disable('topic.move');
            $this->user->perm->disable('topic.label');
            $this->user->perm->disable('topic.delete');
        }

        // USER DOESN'T HAVE PERMISSION TO CREATE TOPICS IN THIS FORUM
        if ($topic['topic_permission'] == 0) {
            $this->user->perm->disable('topic.*');
        }

        // TOPIC IS DELETED
        if ($topic['deleted_id'] != null) {
            $this->user->perm->disable('topic.*');
            $this->user->perm->disable('post.*');
        }

        if ($topic['post_permission'] == 0) {
            $this->user->perm->disable('post.*');
        }

        // PANEL
        $panel = new Panel('/Topic');
        $panel->id($this->url->getID());

        if (LOGGED_USER_ID == $topic['user_id'] and $this->user->perm->has('topic.edit')) {
            $panel->object('tools')->row('edit')->show();
        }

        if ($this->user->perm->has('topic.delete')) {
            $panel->object('tools')->row('delete')->show();
        }

        if ($this->user->perm->has('post.create')) {
            $panel->object('new')->show();
        }

        if ($this->user->perm->has('topic.label')) {
            $panel->object('labels')->show();
        }

        if ($this->user->perm->has('topic.move')) {
            $panel->object('move')->show();
        }

        // TOPIC IS STICKY
        if ($this->user->perm->has('topic.stick')) {
            if ($topic['topic_sticked'] == 1) {
                $panel->object('tools')->row('unstick')->show();
            } else {
                $panel->object('tools')->row('stick')->show();
            }
        }

        // TOPIC IS LOCKED
        if ($this->user->perm->has('topic.lock')) {
            if ($topic['topic_locked'] == 1) {
                $panel->object('tools')->row('unlock')->show();
            } else {
                $panel->object('tools')->row('lock')->show();
            }
        }

        $panel->object('move')->fill(data: $forum->getAllToMove());
        $panel->object('labels')->fill(data: $label->getAll());
        $panel->hideEmpty();
        $this->data->panel = $panel->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_POSTS);
        $pagination->total($post->getParentCount($this->url->getID()));
        $pagination->url($this->url->getURL());
        $post->pagination = $this->data->pagination = $pagination->getData();

        // BLOCK
        $block = new Block('/Topic');

        // IF USER HAS PERMISSION TO CREATE POSTS
        if ($this->user->perm->has('post.create')) {

            // SHOW NEW POST FORM
            $block->object('post')->row('bottom')->show();
        }

        // IF IS THIS FIRST PAGE
        if (PAGE == 1) {

            // SHOW TOPIC
            $block->object('topic')->show();
        }

        $block->object('topic')->appTo(data: $topic, function: function ( \Visualization\Block\Block $block ) {

            // IF TOPIC IS FROM LOGGED USER
            if (LOGGED_USER_ID == $block->obj->get->data('user_id')){
                $block->delButton(['like', 'unlike']);
            }

            // DELETE ALL BUTTONS IF TOPIC IS DELETED OR USER IS NOT LOGGED
            if ($block->obj->get->data('deleted_id') or $this->user->isLogged() === false) {

                $block->delButton();
            }
        });

        $block->object('post')->fill(data: $post->getParent($this->url->getID()), function: function ( \Visualization\Block\Block $block ) use ($post, $topic) {

            $block->obj->set->data('name',  $this->language->get('L_RE') . ': ' . $block->obj->get->data('name'));

            if ($block->obj->get->data('is_like') == true) {
                $block->obj->set->data('likes', $post->getLikes($block->obj->get->data('post_id')));
            }

            // IF IS SET 'SELECT' PARAMETER IN URL
            if ($this->url->is('select')) {

                // IF THIS POST IS SELECTED
                if ($block->obj->get->data('post_id') == $this->url->get('select')) {
                    $block->select();
                }
            }

            if ($this->user->isLogged() === false) {
                $block->delButton();
            }

            if ($block->obj->get->data('report_id') and ($block->obj->get->data('report_status') ?? 0) == 0 and $this->user->perm->has('admin.forum')) {
                $block->notice('reported');
                $block->disable();
            }

            // IF TOPIC OR POST IS DELETED
            if ($topic['deleted_id'] or $block->obj->get->data('deleted_id')) {

                $block->delButton();

                if ($block->obj->get->data('deleted_id')) {

                    $block->notice('deleted');
                    $block->disable();
                    $block->close();
                }

            } else {

                if ($this->user->perm->has('post.create') === false) {
                    $block->delButton('quote');
                }

                // IF THIS POST IS FROM LOGGED USER
                if ($block->obj->get->data('user_id') == LOGGED_USER_ID) {

                    $block->delbutton(['like', 'unlike']);

                    if ($this->user->perm->has('post.edit') === false) {
                        $block->delButton('edit');
                    }

                } else {
                
                    $block->delButton('edit');
                }

                if ($this->user->perm->has('post.delete') === false) {
                    $block->delButton('delete');
                }
            }
        });

        $this->data->block = $block->getData();

        // IF USER HAS PERMISSION TO EDIT TOPIC LABELS
        if ($this->user->perm->has('topic.label')) {
            
            // EDIT LABELS
            $this->process->form(type: '/Topic/Label', on: 'confirm-label', data: [
                'topic_id' => $topic['topic_id']
            ]);
        }

        // IF USER HAS PERMISSION TO MOVE TOPIC
        if ($this->user->perm->has('topic.move')) {
            
            // MOVE TOPIC
            $this->process->form(type: '/Topic/Move', on: 'confirm-forum', data: [
                'user_id'               => $topic['user_id'],
                'topic_id'              => $this->url->getID(),
                'topic_name'            => $topic['topic_name'],
                'current_forum_id'      => (int)$this->url->get('move')
            ]);
        }

        // UPDATE TOPIC VIEWS
        $query->update(TABLE_TOPICS, [
            'topic_views' => [PLUS],
        ], $topic['topic_id']);
    }
}