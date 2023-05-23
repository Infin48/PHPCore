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

namespace App\Page\Custom;

/**
 * Edit
 */
class Edit extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.page';

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Custom/Edit.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // Page
        $page = $db->select('app.page.get()', (int)$this->url->getID()) or $this->error404();

        // Save page data
        $data->set('data.custom', $page);

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Index.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.custom.page_name'))->href('/custom/' . $data->get('data.custom.page_url') . '/');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        $body = @file_get_contents(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/html.html');

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Custom.json');
        $form
            ->form('page')
                ->callOnSuccess($this, 'editCustomPage')
                ->frame('page')
                    ->input('page_html')
                        ->set('data.value', $body = @file_get_contents(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/html.html'));
        $data->form = $form->getDataToGenerate();

        // Set page title
        $data->set('data.head.title', $data->get('data.custom.page_name'));

        // Set page description
        $data->set('data.head.description', $body);
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
    public function editCustomPage( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // File
        $file = new \App\Model\File\File();

        // Create page folder
        $file->mkdir('/Pages/' . $data->get('data.custom.page_id'));

        // Create page folder
        $file->mkdir('/Pages/' . $data->get('data.custom.page_id') . '/Attachments');

        // Create page folder
        $file->mkdir('/Pages/' . $data->get('data.custom.page_id') . '/Images');

        // Edit html
        @file_put_contents(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/html.html', $post->get('page_html'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('page_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect back to page
        $data->set('data.redirect', '/custom/' . $data->get('data.custom.page_id') . '.' . parse($data->get('data.custom.page_name')));
    }
}