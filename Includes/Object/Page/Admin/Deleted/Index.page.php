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

use Model\Pagination;

use Visualization\Lists\Lists;
use Visualization\Block\Block;
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

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $deleted = new Deleted();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(20);
        $pagination->total($deleted->getAllCount());
        $pagination->url($this->getURL());
        $deleted->pagination = $this->data->pagination = $pagination->getData();

        // LIST
        $list = new Lists('Admin/Deleted');
        $list->object('deleted')->fill($deleted->getAll());
        $this->data->list = $list->getData();

        // BLOCK
        $block = new Block('Admin/Deleted/Index');
        $block
            ->object('post')->value($this->system->stats->get('post_deleted'))
            ->object('topic')->value($this->system->stats->get('topic_deleted'))
            ->object('profile_post')->value($this->system->stats->get('profile_post_deleted'))
            ->object('profile_post_comment')->value($this->system->stats->get('profile_post_comment_deleted'));
        $this->data->block = $block->getData();
    }
}