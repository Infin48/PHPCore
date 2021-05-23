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

namespace Page\Admin\Menu\Show;

use Block\Page as BlockPage;
use Block\ButtonSub as _ButtonSub;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Buttonsub
 */
class Buttonsub extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
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
        $dropdown = new _ButtonSub();

        // PAGES
        $pages = $page->getAll();

        // DROPDOWN
        $buttonSub = $dropdown->get($this->getID()) or $this->error();

        // FIELD
        $field = new Field('Admin/Menu/Sub');
        $field->data($buttonSub);
        $field->object('sub')->title('L_MENU_BUTTON_EDIT')
            ->row('page_id')->fill($pages);
        $this->data->field = $field->getData();

        // EDIT SUB BUTTON
        $this->process->form(type: 'Admin/Menu/ButtonSub/Edit', data: [
            'button_sub_id' => $buttonSub['button_sub_id']
        ]);

        $this->data->head['title'] = $this->language->get('L_MENU_BUTTON') . ' - ' . $buttonSub['button_sub_name'];
    }
}