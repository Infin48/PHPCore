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

namespace Page\Admin\Menu\Add;

use Block\Page as BlockPage;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Button
 */
class Button extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'Overall',
        'redirect' => '/admin/menu/',
        'permission' => 'admin.menu'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('menu')->active();
        
        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Menu');
        $this->data->breadcrumb = $breadcrumb->getData();
        
        // BLOCK
        $page = new BlockPage();

        // PAGES
        $pages = $page->getAll();

        // FIELD
        $field = new Field('Admin/Menu/Button');
        $field->object('button')->title('L_MENU_BUTTON_NEW')
            ->row('page_id')->fill($pages);
        $this->data->field = $field->getData();

        // CREATE NEW BUTTON
        $this->process->form(type: 'Admin/Menu/Button/Create');
    }
}