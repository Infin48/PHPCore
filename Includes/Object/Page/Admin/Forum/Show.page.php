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
        'redirect' => '/admin/forum/',
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

        // BLOCK
        $category = new Category();
        $forum = new Forum();

        // CATEGORIES
        $categories = $category->getAll();

        // FORUM
        $forum = $forum->get($this->getID()) or $this->error();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Forum');
        $this->data->breadcrumb = $breadcrumb->getData();
        
        // FIELD
        $field = new Field('Admin/Forum/Forum');
        $field->data($forum);
        $field->object('forum')
            ->title('L_FORUM_EDIT')
            ->row('category_id_new')->show()
            ->fill($categories);
        $this->data->field = $field->getData();

        // EDIT FORUM
        $this->process->form(type: 'Admin/Forum/Edit', data: [
            'forum_id'  => $forum['forum_id']
        ]);

        // PAGE TITLE
        $this->data->head['title'] = $this->language->get('L_FORUM') . ' - ' . $forum['forum_name'];
    }
}