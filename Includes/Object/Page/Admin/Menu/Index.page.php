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
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $button = new Button();
        $buttonSub = new ButtonSub();

        // LIST
        $list = new Lists('Admin/Menu');

        // BUTTONS
        $buttons = $button->getAll();

        $i = 1;
        foreach ($buttons as $item) {

            $list->object('button')->appTo($item)->jumpTo();

            if ($i === 1) {
                $list->delButton('up');
            }

            if ($i === count($buttons)) {
                $list->delButton('down');
            }

            if ((bool)$item['is_dropdown'] === true) {

                // SUB BUTTONS
                $subButtons = $buttonSub->getParent($item['button_id']);

                $x = 1;
                foreach ($subButtons as $subButton) {

                    $list->appTo($subButton)->jumpTo();

                    if ($x === 1) {
                        $list->delButton('up');
                    }
        
                    if ($x === count($subButtons)) {
                        $list->delButton('down');
                    }

                    $x++;
                }
            }
            $i++;
        }
        $this->data->list = $list->getData();
    }
}