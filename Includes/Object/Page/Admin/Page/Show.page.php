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

namespace Page\Admin\Page;

Use Block\Page as BlockPage;

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
        'template' => '/Overall',
        'redirect' => '/admin/page/',
        'permission' => 'admin.page'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('page')->active();
        
        // BLOCK
        $page = new BlockPage();

        // PAGE
        $page = $page->get($this->url->getID()) or $this->error();
        $page['page_html'] = @file_get_contents(ROOT . '/Pages/' . $page['page_id'] . '/html.html');
        $page['page_css'] = @file_get_contents(ROOT . '/Pages/' . $page['page_id'] . '/css.css');

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Page');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/Admin/Page/Page');
        $field->data($page);
        $this->data->field = $field->getData();

        // EDIT PAGE
        $this->process->form(type: '/Admin/Page/Edit', data: [
            'page_id' => $page['page_id']
        ]);

        // TITLE
        $this->data->head['title'] = $this->language->get('L_PAGE') . ' - ' . $page['page_name'];
    }
}