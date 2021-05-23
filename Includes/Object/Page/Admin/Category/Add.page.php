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

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Add
 */
class Add extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'Overall',
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

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Forum');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('Admin/Category/Category');
        $field->object('category')->title('L_CATEGORY_NEW');
        $this->data->field = $field->getData();
        
        // CREATE CATEGORY
        $this->process->form(type: 'Admin/Category/Create');
    }
}