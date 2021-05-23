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

namespace Page\Admin\Ajax;

use Model\Get;

/**
 * Process
 */
class Process extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'loggedIn' => true,
        'permission' => 'admin.?'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $get = new Get();

        $get->get('id') or exit();
        $get->get('process') or exit();

        $type = explode('/', $get->get('process'));
        array_pop($type);
        $type = implode('/', $type);

        if ($this->user->perm->has(match($type) {
            'Page' => 'admin.page',
            'User' => 'admin.user',
            'Forum', 'Category' => 'admin.forum',
            'Group' => 'admin.group',
            'Label' => 'admin.label',
            'Template' => 'admin.template',
            'Menu/Button', 'Menu/ButtonSub' => 'admin.menu',
            'Notification' => 'admin.notification',
            'Template' => 'admin.template',
            'Deleted/Post', 'Deleted/Topic', 'Deleted/ProfilePost', 'Deleted/ProfilePostComment' => 'admin.forum',
        }) === false) {
            exit();
        }

        $id = match($type) {
            'Page' => 'page_id',
            'User' => 'user_id',
            'Forum' => 'forum_id',
            'Group' => 'group_id',
            'Label' => 'label_id',
            'Category' => 'category_id',
            'Template' => 'template_name_folder',
            'Menu/Button' => 'button_id',
            'Notification' => 'notification_id',
            'Menu/ButtonSub' => 'button_sub_id',
            'Template' => 'template_name_folder',
            'Deleted/Post', 'Deleted/Topic', 'Deleted/ProfilePost', 'Deleted/ProfilePostComment' => 'deleted_id',
            default => exit()
        };
        
        if ($this->process->call(type: 'Admin/' . $get->get('process'), mode: 'direct', data: [
            $id => $get->get('id')
        ])) {
            $this->data->data([
                'status' => 'ok',
                'redirect' => $this->process->getURL() ? $this->system->url->build($this->process->getURL()) : ''
            ]);
        }
    }
}