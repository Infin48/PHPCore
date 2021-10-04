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

use Block\Button as BlockButton;

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
        $button = new BlockButton();

        // BUTTON
        $button = $button->get($this->url->getID()) or $this->error();

        // BUTTON LINK TYPE
        $linkType = match ($button['button_link_type']) {
            1 => 'local',
            2 => 'external'
        };

        // FIELD
        $field = new Field('/Admin/Menu/Button');
        $field->data($button);
        $field->object('button')->title('L_MENU_BUTTON_EDIT')
            ->row('button_link_type')->option($linkType)->check();
        $this->data->field = $field->getData();

        // EDIT BUTTON
        $this->process->form(type: '/Admin/Menu/Button/Edit', data: [
            'button_id' => $button['button_id']
        ]);

        $this->data->head['title'] = $this->language->get('L_MENU_BUTTON') . ' - ' . $button['button_name'];

    }
}