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

namespace Page\Admin\Notification;

use Block\Admin\Notification;

use Visualization\Admin\Lists\Lists;
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
        'template' => '/Overall',
        'permission' => 'admin.notification'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('notification')->active();
        
        // BLOCK
        $notification = new Notification();
        
        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();
        
        // LIST
        $list = new Lists('/Notification');
        $list->object('notification')->fill(data: $notification->getAll(), function: function ( \Visualization\Admin\Lists\Lists $list, int $i, int $count ) { 

            if ($i === 1) {
                $list->delButton('up');
            }

            if ($i === $count) {
                $list->delButton('down');
            }
        });
        $this->data->list = $list->getData();
    }
}