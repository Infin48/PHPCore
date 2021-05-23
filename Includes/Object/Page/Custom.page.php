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

namespace Page;

use Block\Page as Blockpage;

use Visualization\Breadcrumb\Breadcrumb;

/**
 * Custom 
 */
class Custom extends Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BLOCK
        $page = new Blockpage();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Index');
        $this->data->breadcrumb = $breadcrumb->getData();

        // PAGE
        $page = $page->get((int)$this->getID()) or $this->error();

        $this->data->data([
            'body' => @file_get_contents(ROOT . '/Pages/' . $page['page_id'] . '/html.html'),
            'page_id' => $page['page_id']
        ]);

        // HEAD
        $this->data->head['title'] = $page['page_name'];
    }
}