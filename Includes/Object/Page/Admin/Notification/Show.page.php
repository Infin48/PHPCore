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

use Block\Notification;

use Visualization\Field\Field;
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
        'redirect' => '/admin/notification/',
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

        // NOTIFICATION
        $notification = $notification->get($this->getID()) or $this->error();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Notification');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('Admin/Notification');
        $field->data($notification);
        $field->object('notification')->title('L_NOTIFICATION_EDIT');
        $this->data->field = $field->getData();

        // EDIT NOTIFICATION
        $this->process->form(type: 'Admin/Notification/Edit', data: [
            'notification_id' => $notification['notification_id']
        ]);

        $this->data->head['title'] = $this->language->get('L_NOTIFICATION') . ' - ' . $notification['notification_name'];
    }
}