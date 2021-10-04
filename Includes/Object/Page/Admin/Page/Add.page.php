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

namespace Page\Admin\Page;

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
        'template' => 'page/page',
        'redirect' => '/admin/page/',
        'permission' => 'admin.page'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    public function body()
    {
        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Page');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/Admin/Page');
        $field->object('page')->title('L_PAGE_NEW');
        $this->data->field = $field->getData();

        // CREATE PAGE
        $this->process->form(type: '/Admin/Page/Create');
    }
}