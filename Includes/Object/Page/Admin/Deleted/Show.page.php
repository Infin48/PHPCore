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

namespace Page\Admin\Deleted;

use Block\Deleted;
use Block\Admin\Post;
use Block\Admin\Topic;
use Block\Admin\ProfilePost;
use Block\Admin\ProfilePostComment;

use Visualization\Field\Field;
use Visualization\Block\Block;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Show
 */
class Show extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'template' => 'Overall',
        'permission' => 'admin.forum'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('forum')->row('deleted')->active();
        
        // BLOCK
        $deleted = new Deleted();

        // REPORT DATA
        $data = $deleted->get($this->getID()) or $this->error();

        // FIELD
        $field = new Field('Admin/Deleted');

        // ASSIGN DATA BASED ON TYPE
        switch ($data['deleted_type']) {
            case 'Post':
                $content = (new Post)->get($data['deleted_type_id']);

                $field->object('show')->setOptions('type', 'Deleted/Post')
                    ->row('post_id')->show();
            break;
            case 'Topic': 
                $content = (new Topic)->get($data['deleted_type_id']);

                $field->object('show')->setOptions('type', 'Deleted/Topic')
                    ->row('topic_id')->show();
            break;
            case 'ProfilePost': 
                $content = (new ProfilePost)->get($data['deleted_type_id']);

                $field->object('show')->setOptions('type', 'Deleted/ProfilePost')
                    ->row('profile_post_id')->show();
            break;
            case 'ProfilePostComment': 
                $content = (new ProfilePostComment)->get($data['deleted_type_id']);

                $field->object('show')->setOptions('type', 'Deleted/ProfilePostComment')
                    ->row('profile_post_comment_id')->show();
            break;

            default:
                redirect('/admin/deleted/');
            break;
        }
        if (empty($content)) {
            redirect('/admin/deleted/');
        }
        $data = array_merge($content, $data);

        // URL TO DELETED CONTENT
        $field->object('show')->row('show')->setData('href', '$' . $this->build->url->{lcfirst($data['deleted_type'])}($data));
        $field->data($data);
        $field->disButtons();
        $this->data->field = $field->getData();

        // BLOCK
        $block = new Block('Admin/Deleted/Show');
        $block
            ->object('type')->value($this->language->get('L_CONTENT_LIST')[$data['deleted_type']])
            ->object('id')->value($data['deleted_id'])
            ->object('deleted')->value($this->build->date->long($data['deleted_created']))
            ->object('deleted_by')->value($data['user_name']);
        $this->data->block = $block->getData();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Deleted');
        $this->data->breadcrumb = $breadcrumb->getData();
    }
}