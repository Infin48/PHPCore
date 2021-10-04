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

namespace Page\Admin\Category;

use Block\Group;
use Block\Admin\Category;

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
        'redirect' => '/admin/forum/',
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
        $this->navbar->object('forum')->row('forum')->active();
        
        // BLOCK
        $group = new Group();
        $category = new Category();

        // GET FORUM DATA
        $_category = $category->get($this->url->getID()) or $this->error();

        // SEE PERMISSION
        $_category['see'] = $category->getSee($this->url->getID());

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Forum');
        $this->data->breadcrumb = $breadcrumb->getData(); 

        // FIELD
        $field = new Field('/Admin/Category/Permission');
        $field->data($_category);
        $field->object('groups')->fill(data: array_merge($group->getAll(), [0 => [
            'group_id' => 0,
            'group_name' => $this->language->get('L_GROUP_VISITOR'),
            'group_color' => '#4e4e4e',
            'group_class_name' => 'visitor'
        ]]));
        $this->data->field = $field->getData();

        // EDIT FORUM PERMISSION
        $this->process->form(type: '/Admin/Category/Permission', data: [
            'category_id' => $this->url->getID()
        ]);

        // PAGE TITLE
        $this->data->head['title'] = $this->language->get('L_CATEGORY') . ' - ' . $_category['category_name'];
    }
}