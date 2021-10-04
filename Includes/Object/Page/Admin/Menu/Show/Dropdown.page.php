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

use Block\Dropdown as BlockDropdown;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Dropdown
 */
class Dropdown extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'template' => '/Overall',
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
        $breadcrumb = new Breadcrumb('/Admin/Menu');
        $this->data->breadcrumb = $breadcrumb->getData();
        
        // BLOCK
        $dropdown = new BlockDropdown();

        // BUTTON
        $button = $dropdown->get($this->url->getID()) or $this->error();

        // FIELD
        $field = new Field('/Admin/Menu/Dropdown');
        $field->data($button);
        $field->object('dropdown')->title('L_MENU_DROPDOWN_EDIT');
        $this->data->field = $field->getData();

        // EDIT DROPDOWN
        $this->process->form(type: '/Admin/Menu/Dropdown/Edit', data: [
            'button_id' => $button['button_id'],
            'button_name' => $button['button_name']
        ]);

        $this->data->head['title'] = $this->language->get('L_MENU_DROPDOWN') . ' - ' . $button['button_name'];
    }
}