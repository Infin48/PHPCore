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
 * Show
 */
class Show extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var bool $photoSwipe If true - JS library PhotoSwipe will be loaded 
     */
    protected bool $photoSwipe = true;
    
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.page';

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // Language
        $language = $data->get('inst.language');
        
        // Navbar
        $this->navbar->elm1('settings')->elm2('page')->active();
        
        // Page data
        $row = $db->select('app.page.get()', $this->url->getID()) or $this->error404();

        // Save page data
        $data->set('data.custom', $row);

        // Load page HTML
        $data->set('data.custom.page_html', @file_get_contents(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/html.html'));

        // Load page CSS
        $data->set('data.custom.page_css', @file_get_contents(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/css.css'));

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Page.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.custom.page_name'))->href('/admin/page/show/' . $data->get('data.custom.page_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // File model
        $file = new \App\Model\File\File();

        $images = [];
        // Search images
        $file->getFiles(
            path: '/Pages/' . $this->url->getID() . '/Images/*',
            function: function ( \App\Model\File\File $file, string $path ) use (&$images)
            {
                $size = getimagesize($path);
                $images[] = [
                    'type' => 'image',
                    'path' => str_replace(ROOT, '', $path),
                    'width' => $size[0] ?? 1920,
                    'height' => $size[1] ?? 1080
                ];
            }
        );

        $attachments = [];
        // Search attachments
        $file->getFiles(
            path: '/Pages/' . $this->url->getID() . '/Attachments/*',
            flag: \App\Model\File\File::SORT_BY_DATE,
            function: function ( \App\Model\File\File $file, string $path ) use (&$attachments)
            {
                $ex = explode('/', str_replace(ROOT, '', $path));
                $filter = array_filter($ex);
                $attachments[] = [
                    'name' => array_pop($filter),
                    'path' => str_replace(ROOT, '', $path)
                ];
            }
        );

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Page/Page.json');
        $form
            ->form('page')
                ->data($data->get('data.custom'))
                ->callOnSuccess($this, 'editCustomPageThroughAdminPanel')
                ->frame('page')
                    ->input('show')
                        ->set('data.href', $this->url->build('/custom/' . $data->get('data.custom.page_url')))
                    ->input('page_images')->fill($images)
                    ->input('page_attachments')->fill($attachments);
        $data->form = $form->getDataToGenerate();

        // Page title
        $data->set('data.head.title', $language->get('L_PAGE.L_PAGE') . ' - ' . $data->get('data.custom.page_name'));
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
    public function editCustomPageThroughAdminPanel( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Create page folder
        @mkdir(ROOT . '/Pages/' . $data->get('data.custom.page_id'));

        // Create page folder
        @mkdir(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/Attachments');

        // Create page folder
        @mkdir(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/Images');
        
        // Edit html
        file_put_contents(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/html.html', $post->get('page_html'));

        // Edit css
        file_put_contents(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/css.css', $post->get('page_css'));

        // Edit page
        $db->update(TABLE_PAGES, [
            'page_url' => $db->lastInsertId() . '.' . parse($post->get('page_name')),
            'page_name' => $post->get('page_name')
        ], $data->get('data.custom.page_id'));

        $file = new \App\Model\File\File();

        // Save every uploaded attachment
		foreach ($post->get('page_attachments') as $attachment)
		{
            $attachment->upload('/Pages/' . $data->get('data.custom.page_id') . '/Attachments');
		}

        // Save every uploaded image
		foreach ($post->get('page_images') as $image)
		{
            $image->upload('/Pages/' . $data->get('data.custom.page_id') . '/Images');
		}

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('page_name') );
    }
}