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

namespace Page\Admin\Forum;

use Block\Admin\Forum;
use Block\Admin\Category;

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
        'permission' => 'admin.forum'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('forum')->row('forum')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $forum = new Forum();
        $category = new Category();

        // LIST
        $list = new Lists('/Forum');
        $list->object('forum')->fill(data: $category->getAll(), function: function ( \Visualization\Admin\Lists\Lists $list, int $i, int $count ) use ($forum) { 

            if ($i === 1) {
                $list->delButton('up');
            }

            if ($i === $count) {
                $list->delButton('down');
            }

            $list->fill(data: $forum->getParent($list->obj->get->data('category_id')), function: function ( \Visualization\Admin\Lists\Lists $list, int $i, int $count ) { 

                if ($i === 1) {
                    $list->delButton('up');
                }
    
                if ($i === $count) {
                    $list->delButton('down');
                }
            });
        });
        $this->data->list = $list->getData();
    }
}