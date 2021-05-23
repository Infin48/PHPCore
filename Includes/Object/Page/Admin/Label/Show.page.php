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

use Block\Label;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Show
 */
class Show extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'template' => 'Overall',
        'redirect' => '/admin/label/',
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
        $breadcrumb = new Breadcrumb('Admin/Label');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $label = new Label();

        $label = $label->get($this->getID()) or $this->error();

        // FIELD
        $field = new Field('Admin/Label/Label');
        $field->data($label);
        $this->data->field = $field->getData();
        
        // EDIT LABEL
        $this->process->form(type: 'Admin/Label/Edit', data: [
            'label_id' => $this->getID()
        ]);

        // PAGE TITLE
        $this->data->head['title'] = $this->language->get('L_LABEL') . ' - ' . $label['label_name'];
    }
}