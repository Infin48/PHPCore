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
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // BLOCK
        $forum = new Forum();
        $category = new Category();

        // LIST
        $list = new Lists('Admin/Forum');

        // CATEGORIES
        $categories = $category->getAll();

        $i = 1;
        foreach ($categories as $_category) {

            $list->object('forum')->appTo($_category)->jumpTo();

            if ($i == 1) {
                $list->delButton('up');
            }

            if ($i === count($categories)) {
                $list->delButton('down');
            }

            // FORUMS
            $forums = $forum->getParent($_category['category_id']);

            $x = 1;
            foreach ($forums as $_forum) {
                $list->appTo($_forum)->jumpTo();

                if ($x == 1) {
                    $list->delButton('up');
                }

                if ($x === count($forums)) {
                    $list->delButton('down');
                }

                $x++;
            }
            $i++;
        }
        $this->data->list = $list->getData();
    }
}