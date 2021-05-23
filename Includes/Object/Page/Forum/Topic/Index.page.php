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
        $topic = $_topic->get($this->getID()) or $this->error();

        // GET TOPIC LABELS
        $topic['labels'] = $_topic->getLabels($this->getID());

        // GET TOPIC LIKES
        $topic['likes'] = $_topic->getLikes($this->getID());

        $this->data->data([
            'labels' => $topic['labels'],
            'topic_id' => $topic['topic_id'],
            'report_id' => $topic['report_id'],
            'is_locked' => $topic['is_locked'],
            'topic_name' => $topic['topic_name'],
            'deleted_id' => $topic['deleted_id'],
            'topic_image' => $topic['topic_image'],
            'report_status' => $topic['report_status'] ?? 0,
        ]);

        if ($topic['topic_image']) {
            $topic['image_url'] = '/Uploads/Topic/' . $topic['topic_id'] . '.' . $topic['topic_image'];
        }

        // HEAD
        $this->data->head = [
            'title'         => $topic['topic_name'],
            'description'   => $topic['topic_text']
        ];

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Forum/Topic');
        $breadcrumb->object('category')->title('$' . $topic['category_name']);
        $breadcrumb->object('forum')->title('$' . $topic['forum_name']);
        $breadcrumb->object('forum')->href($this->build->url->forum($topic));
        $this->data->breadcrumb = $breadcrumb->getData();
        
        // TOPIC IS NOT FROM LOGGED USER
        if ($topic['user_id'] != LOGGED_USER_ID) {
            $this->user->perm->disable('topic.edit');
        }

        // TOPIC IS LOCKED
        if ($topic['is_locked'] == 1) {
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
        $panel = new Panel('Topic');

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
            if ($topic['is_sticky'] == 1) {
                $panel->object('tools')->row('unstick')->show();
            } else {
                $panel->object('tools')->row('stick')->show();
            }
        }

        // TOPIC IS LOCKED
        if ($this->user->perm->has('topic.lock')) {
            if ($topic['is_locked'] == 1) {
                $panel->object('tools')->row('unlock')->show();
            } else {
                $panel->object('tools')->row('lock')->show();
            }
        }

        $panel->object('move')->fill($forum->getAllToMove($topic['forum_id']));
        $panel->object('labels')->fill($label->getAll());
        $panel->hideEmpty();
        $this->data->panel = $panel->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_POSTS);
        $pagination->total($post->getParentCount($this->getID()));
        $pagination->url($this->getURL());
        $post->pagination = $this->data->pagination = $pagination->getData();

        // BLOCK
        $block = new Block('Topic');
        $block->object('topic')->appTo($topic)->jumpTo();

        // IF TOPIC IS FROM LOGGED USER
        if (LOGGED_USER_ID == $topic['user_id']){
            $block->delButton(['like', 'unlike']);
        }

        // DELETE ALL BUTTONS IF TOPIC IS DELETED OR USER IS NOT LOGGED
        if ($topic['deleted_id'] or $this->user->isLogged() === false) {

            $block->delButton();
        }

        if ($this->user->perm->has('post.create')) {

            $block->object('post')->row('bottom')->show();
        }

        // IF IS THIS FIRST PAGE
        if (PAGE == 1) {

            // SHOW TOPIC
            $block->object('topic')->show();
        }

        foreach ($post->getParent($this->getID()) as $item) {

            // IF POST HAS ANY LIKES
            if ((bool)$item['is_like'] === true) {
                $item['likes'] = $post->getLikes($item['post_id']);
            }
            
            // APPEND POST TO BLOCK
            $block->object('post')->appTo($item)->jumpTo();

            // IF IS SET 'SELECT' PARAMETER IN URL
            if ($this->url->is('select')) {

                // IF THIS POST IS SELECTED
                if ($item['post_id'] == $this->url->get('select')) {
                    $block->select();
                }
            }

            if ($this->user->isLogged() === false) {
                $block->delButton();
            }

            if ($item['report_id'] and ($item['report_status'] ?? 0) == 0 and $this->user->perm->has('admin.forum')) {
                $block->notice('reported');
                $block->disable();
            }

            // IF TOPIC OR POST IS DELETED
            if ($topic['deleted_id'] or $item['deleted_id']) {

                $block->delButton();

                if ($item['deleted_id']) {

                    $block->notice('deleted');
                    $block->disable();
                    $block->close();
                }

                continue;
            }

            if ($this->user->perm->has('post.create') === false) {
                $block->delButton('quote');
            }

            // IF THIS POST IS FROM LOGGED USER
            if ($item['user_id'] == LOGGED_USER_ID) {

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
        $this->data->block = $block->getData();

        // IF USER HAS PERMISSION TO EDIT TOPIC LABELS
        if ($this->user->perm->has('topic.label')) {
            
            // EDIT LABELS
            $this->process->form(type: 'Topic/Label', on: 'confirm', data: [
                'topic_id' => $topic['topic_id']
            ]);
        }

        if ($this->user->perm->has('topic.move')) {

            if ((int)$this->url->get('move') != $topic['forum_id']) {

                // MOVE TOPIC
                $this->process->call(type: 'Topic/Move', mode: 'silent', on: $this->url->is('move'), data: [
                    'user_id'       => $topic['user_id'],
                    'topic_id'      => $this->getID(),
                    'forum_id'      => (int)$this->url->get('move'),
                    'topic_name'    => $topic['topic_name']
                ]);
            }
        }

        if ($this->user->perm->has('topic.delete')) {

            // DELETE TOPIC
            $this->process->call(type: 'Topic/Delete', mode: 'silent', on: $this->url->is('delete'), data: ['topic_id' => $this->getID()]);
        }

        if ($topic['is_locked'] == 0) {

            if ($this->user->perm->has('topic.lock')) {

                // LOCK TOPIC
                $this->process->call(type: 'Topic/Lock', mode: 'silent', on: $this->url->is('lock'), data: ['topic_id' => $this->getID()]); 
            }
        }

        if ($topic['is_locked'] == 1) {

            if ($this->user->perm->has('topic.lock')) {

                // UNLOCK TOPIC
                $this->process->call(type: 'Topic/Unlock', mode: 'silent', on: $this->url->is('unlock'), data: ['topic_id' => $this->getID()]);
            }
        }

        if ($topic['is_sticky'] == 0) {
        
            if ($this->user->perm->has('topic.stick')) {

                // STICK TOPIC
                $this->process->call(type: 'Topic/Stick', mode: 'silent', on: $this->url->is('stick'), data: ['topic_id' => $this->getID()]);
            }
        }

        if ($topic['is_sticky'] == 1) {

            if ($this->user->perm->has('topic.stick')) {

                // UNSTICK TOPIC
                $this->process->call(type: 'Topic/Unstick', mode: 'silent', on: $this->url->is('unstick'), data: ['topic_id' => $this->getID()]);
            }
        }

        // UPDATE TOPIC VIEWS
        $query->update(TABLE_TOPICS, [
            'topic_views' => [PLUS],
        ], $topic['topic_id']);
    }
}