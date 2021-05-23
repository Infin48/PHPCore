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

namespace Page\Admin\Update;

use Visualization\Field\Field;
use Visualization\Block\Block;
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
        'permission' => 'admin.settings'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('other')->row('update')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('Admin/Update');
        $field->disButtons();
        $field->object('latest')->show();
        $this->data->field = $field->getData();

        // BLOCK
        $block = new Block('Admin/Update');
        $block
            ->object('version')->value($this->system->settings->get('site.version'))
            ->object('last_updated')->value($this->build->date->short($this->system->settings->get('site.updated')));
        $this->data->block = $block->getData();
    }
}