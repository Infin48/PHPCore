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

namespace Page\Admin\User;

use Block\User;

use Model\Pagination;

use Visualization\Admin\Lists\Lists;
use Visualization\Field\Field;
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
        'permission' => 'admin.user'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('user')->active();

        // BLOCK
        $user = new User();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // PAGINATION
        $pagination = new Pagination();
        $pagination->max(20);
        $pagination->total($user->getAllCount());
        $pagination->url($this->url->getURL());
        $user->pagination = $this->data->pagination = $pagination->getData();

        // LIST
        $field = new Field('/Admin/User/Index');
        $this->data->field = $field->getData();

        // LIST
        $list = new Lists('/User');
        $list->object('user')->fill(data: $user->getAll(), function: function ( \Visualization\Admin\Lists\Lists $list ) { 

            if ($this->user->perm->compare(index: $list->obj->get->data('group_index'), admin: $list->obj->get->data('user_admin')) === false) {

                $list->delButton('edit');
            }
        });
        $this->data->list = $list->getData();

        // SEARCH USER
        $this->process->form(type: '/Admin/User/Search');
    }
}