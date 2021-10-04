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

namespace Page\Admin\Report;

use Block\Report;

use Visualization\Admin\Lists\Lists;
use Visualization\Admin\Block\Block;
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
        'template' => '/Report',
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
        $this->navbar->object('forum')->row('reported')->active()->option('index')->active();

        // REPORT BLOCK
        $report = new Report();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // LIST
        $list = new Lists('/Report/Index');
        $list->object('last')->fill(data: $report->getLastPending())
            ->object('users')->fill(data: $report->getUsers())
            ->object('solved')->fill(data: $report->getLastSolved());
        $this->data->list = $list->getData();

        // REPORT STATS
        $stats = $report->getStats();

        // BLOCK
        $block = new Block('/Report/Index');
        $block
            ->object('post')->value($stats['post'])
            ->object('topic')->value($stats['topic'])
            ->object('profile_post')->value($stats['profile_post'])
            ->object('profile_post_comment')->value($stats['profile_post_comment']);
        $this->data->block = $block->getData();

        // CHANGE REPORT TYPE
        $this->process->form(type: '/Admin/Report/Change', on: 'change');
    }
}