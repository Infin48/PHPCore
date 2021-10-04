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

namespace Page\Admin\Settings;

use Block\Settings;

use Visualization\Field\Field;
use Visualization\Admin\Lists\Lists;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Url
 */
class Url extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Overall',
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
        $this->navbar->object('settings')->row('settings')->active()->option('url')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $settings = new Settings();

        // LIST
        $list = new Lists('/Settings/URL');
        $list->object('defaults')->fill(data: $settings->getURLDefault());
        $list->object('hidden')->fill(data: $settings->getURLHidden());
        $this->data->list = $list->getData();

        // FIELD
        $field = new Field('/Admin/Settings/URL');
        $this->data->field = $field->getData();

        // NEW LABEL
        $this->process->form(type: '/Admin/Settings/URL/Create');
    }
}