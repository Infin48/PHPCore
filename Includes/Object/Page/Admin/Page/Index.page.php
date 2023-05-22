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

namespace App\Page\Admin\Page;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.page';

    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    protected function ajax( string $ajax )
    {
        return match($ajax)
        {
            'run/page/delete' => 'deleteCustomPage',

            default => ''
        };
    }

    /**
     * Load data according to received ajax
     *
     * @param  string $ajax Received ajax
     * 
     * @return array Data
     */
    public function ajaxData( string $ajax )
    {
        return match($ajax)
        {
            'run/page/delete' => [
                'id' => STRING
            ],

            default => []
        };
    }

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // Navbar
        $this->navbar->elm1('settings')->elm2('page')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Page.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Page/Index.json');
        $form->form('page')->callOnSuccess($this, 'newCustomPage');
        $data->form = $form->getDataToGenerate();

        // List of pages
        $pages = $db->select('app.page.all()');

        // Save list of page's ids
        $data->set('data.pages', array_column($pages, 'page_id'));

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Page.json');
        $list->elm1('page')->fill(data: $pages, function: function( \App\Visualization\ListsAdmin\ListsAdmin $list )
        {
            $list
                ->set('data.title', $list->get('data.page_name'))
                ->set('data.html.ajax-id', $list->get('data.page_id'))
                ->set('data.button.show.href', '/custom/' . $list->get('data.page_id') . '.' . $list->get('data.page_url'))
                ->set('data.button.edit.href', '/admin/page/show/' . $list->get('data.page_id'));
        });
        $data->list = $list->getDataToGenerate();
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function newCustomPage( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Add page
        $db->insert(TABLE_PAGES, [
            'page_name' => $post->get('page_name'),
            'page_url' => parse($post->get('page_name'))
        ]);

        // Create page folder
        @mkdir(ROOT . '/Pages/' . $db->lastInsertId());

        // Create page folder
        @mkdir(ROOT . '/Pages/' . $db->lastInsertId() . '/Attachments');

        // Create page folder
        @mkdir(ROOT . '/Pages/' . $db->lastInsertId() . '/Images');

        // Html file
        @file_put_contents(ROOT . '/Pages/' . $db->lastInsertId() . '/html.html', '');

        // Css file
        @file_put_contents(ROOT . '/Pages/' . $db->lastInsertId() . '/css.css', '');

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('page_name') );
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function deleteCustomPage( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if role exists
        if (!in_array($post->get('id'), $data->get('data.pages')))
        {
            return false;
        }

        $file = new \App\Model\File\File();
        $file->delete('/Pages/' .  $post->get('id') . '/*');

        // Delete page
        $db->delete(
            table: TABLE_PAGES,
            id: $post->get('id')
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Refresh page
        $data->set('options.refresh', true);
    }
}