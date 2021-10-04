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

namespace Page\Admin\Group;

use Block\Group;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Permission
 */
class Permission extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'template' => '/Overall',
        'redirect' => '/admin/group/',
        'permission' => 'admin.group'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('group')->active();
        
        // BLOCK
        $group = new Group();

        // GROUP
        $group = $group->get($this->url->getID()) or $this->error();

        // IF LOGGED USER DOENSN'T HAVE PERMISISON TO EDIT THIS GROUP 
        $this->user->perm->index($group['group_index']) or $this->redirect();

        // DATA TO TEMPLATE
        $this->data->data([
            'group_permission' => $group['group_permission']
        ]);

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Group');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/Admin/Group/Permission');
        $field->data($group);
        $this->data->field = $field->getData();

        // EDIT GROUP
        $this->process->form(type: '/Admin/Group/Permission', data: [
            'group_id' => $group['group_id'],
            'group_name' => $group['group_name']
        ]);

        // PAGE TITLE
        $this->data->head['title'] = $this->language->get('L_GROUP') . ' - ' . $group['group_name'];
    }
}