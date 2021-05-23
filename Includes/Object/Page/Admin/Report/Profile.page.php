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

use Model\Pagination;

use Visualization\Lists\Lists;
use Visualization\Block\Block;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Profile
 */
class Profile extends \Page\Page
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
        $this->navbar->object('forum')->row('reported')->active()->option('profilepost')->active();

        // REPORT BLOCK
        $report = new Report();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Report/Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // REPORT STATS
        $stats = $report->getStats();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(MAX_REPORTED_PROFILE_POSTS);
        $pagination->total($stats['profile_post']);
        $pagination->url($this->getURL());
        $report->pagination = $this->data->pagination = $pagination->getData();

        // LIST
        $list = new Lists('Admin/Report/ProfilePost');
        $list->object('profilepost')->fill($report->getAllProfilePost());
        $this->data->list = $list->getData();

        // BLOCK
        $block = new Block('Admin/Report/ProfilePost');
        $block->object('profile_post')->value($stats['profile_post']);
        $this->data->block = $block->getData();
    }
}