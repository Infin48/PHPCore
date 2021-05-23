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

namespace Page\Admin;

use Block\Log as LogBlock;

use Model\Pagination;

use Visualization\Lists\Lists;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Log
 */
class Log extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'Overall',
        'permission' => 'admin.?'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('other')->row('log')->active();

        // BLOCK
        $log = new LogBlock();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(20);
        $pagination->total($log->getAllCount());
        $pagination->url($this->getURL());
        $log->pagination = $this->data->pagination = $pagination->getData();

        // LIST
        $list = new Lists('Admin/Log');
        $list->object('log')->fill($log->getAll());
        $this->data->list = $list->getData();
    }
}