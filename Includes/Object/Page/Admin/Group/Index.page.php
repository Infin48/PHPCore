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

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/Admin/Group/Index');
        $this->data->field = $field->getData();

        // GROUPS
        $groups = $group->getAll();
        
        // LIST
        $list = new Lists('/Group');
        $list->object('group')->fill(data: $groups, function: function ( \Visualization\Admin\Lists\Lists $list, int $i, int $count ) use ($groups) { 

            if ($this->user->perm->index($list->obj->get->data('group_index')) === false) {

                $list->disable()
                    ->delButton([
                        'up',
                        'down',
                        'edit',
                        'delete',
                        'permission'
                    ]);
            }

            if ($i == 1 or $groups[$i - 2]['group_index'] >= $this->user->get('group_index')) {
                $list->delButton('up');
            }

            if ($list->obj->get->data('group_id') == $this->system->get('default_group')) {

                $list->delButton('delete');
            }

            if ($i === $count) {
                $list->delButton('down');
            }
        });
        $this->data->list = $list->getData();

        // CREATE NEW GROUP
        $this->process->form(type: '/Admin/Group/Create');
    }

}