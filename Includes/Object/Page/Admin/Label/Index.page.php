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

namespace Page\Admin\Label;

use Block\Label as Label;

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
        'permission' => 'admin.label'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('forum')->row('label')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $label = new Label();

        // LIST
        $list = new Lists('/Label');
        $list->object('label')->fill(data: $label->getAll(), function: function ( \Visualization\Admin\Lists\Lists $list, int $i, int $count ) { 

            if ($i === 1) {
                $list->delButton('up');
            }

            if ($i === $count) {
                $list->delButton('down');
            }
        });
        $this->data->list = $list->getData();

        // FIELD
        $field = new Field('/Admin/Label/Label');
        $this->data->field = $field->getData();

        // NEW LABEL
        $this->process->form(type: '/Admin/Label/Create');
    }
}