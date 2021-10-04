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

namespace Page\Admin\Menu;

use Block\Button;
use Block\ButtonSub;

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
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $button = new Button();
        $buttonSub = new ButtonSub();

        // LIST
        $list = new Lists('/Menu');
        $list->object('button')->fill(data: $button->getAll(), function: function ( \Visualization\Admin\Lists\Lists $list, int $i, int $count ) use ($buttonSub) { 

            if ($i === 1) {
                $list->delButton('up');
            }

            if ($i === $count) {
                $list->delButton('down');
            }

            if ($list->obj->get->data('button_dropdown') == 1) {

                $list->fill(data: $buttonSub->getParent($list->obj->get->data('button_id')), function: function ( \Visualization\Admin\Lists\Lists $list, int $i, int $count ) { 

                    if ($i === 1) {
                        $list->delButton('up');
                    }
        
                    if ($i === $count) {
                        $list->delButton('down');
                    }
                });
            }
        });
        $this->data->list = $list->getData();
    }
}