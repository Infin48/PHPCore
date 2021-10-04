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

use Block\Page as BlockPage;

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
        'permission' => 'admin.page'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('page')->active();

        // BLOCK
        $page = new BlockPage();
        
        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/Admin/Page/Index');
        $this->data->field = $field->getData();

        // LIST
        $list = new Lists('/Page');
        $list->object('page')->fill(data: $page->getAll());
        $this->data->list = $list->getData();

        // CREATE NEW PAGE
        $this->process->form(type: '/Admin/Page/Create');
    }
}