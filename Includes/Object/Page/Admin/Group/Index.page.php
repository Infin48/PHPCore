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
use Visualization\Lists\Lists;
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
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('Admin/Group/Index');
        $this->data->field = $field->getData();

        // LIST
        $list = new Lists('Admin/Group');

        // GROUPS
        $groups = $group->getAll();

        $i = 1;
        $cache = $groups[0]['group_index'];
        foreach ($groups as $group) {

            $list->object('group')->appTo($group)->jumpTo();
            
            if ($this->user->perm->index($group['group_index']) === false) {

                $list->disable()
                    ->delButton([
                        'up',
                        'down',
                        'edit',
                        'delete',
                        'permission'
                    ]);
            }

            if ($cache >= $this->user->get('group_index') or $i === 1) {

                $list->delButton('up');
            }

            if ($i === count($groups)) {
                $list->delButton('down');
            }

            if ($group['group_id'] == $this->system->settings->get('default_group')) {

                $list->delButton('delete');
            }

            $cache = $group['group_index'];
            $i++;
        }

        $this->data->list = $list->getData();

        // CREATE NEW GROUP
        $this->process->form(type: 'Admin/Group/Create');
    }

}