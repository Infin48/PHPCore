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
 * Index
 */
class Index extends \App\Page\Page
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
    protected string $template = 'Root/Style:/Templates/Custom/Custom.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        $id = $this->url->getID();
        if (!$id and $system->get('site.mode') == 'static')
        {
            $id = $system->get('site.mode.static.index') ?: 0;
        }
        
        // Page
        $row = $db->select('app.page.get()', $id) or $this->error404();

        // Save page data
        $data->set('data.custom', $row);

        // Save current URL
        $this->url->set('/custom/' . $id . '.' . $data->get('data.custom.page_url'));

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Index.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.custom.page_name'))->href('/custom/' . $data->get('data.custom.page_id') . '/');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // If logged user has permission to manage custom pages
        if ($permission->has('admin.page'))
        {
            // Panel
            $panel = new \App\Visualization\Panel\Panel('Root/Panel:/Formats/Custom.json');
            $panel->elm1('edit-admin')->set('data.href', '/admin/page/show/' . $data->get('data.custom.page_id'));
            $data->panel = $panel->getDataToGenerate();
        }

        // File model
        $file = new \App\Model\File\File();

        $images = [];
        // Search for templates
        $file->getFiles(
            path: '/Pages/' . $id . '/Images/*',
            function: function ( \App\Model\File\File $file, string $path ) use (&$images)
            {
                $size = getimagesize($path);
                $images[] = [
                    'path' => str_replace(ROOT, '', $path),

                    // Set default sizes for SVG images
                    'width' => $size[0] ?? 1920,
                    'height' => $size[1] ?? 1080
                ];
            }
        );

        $attachments = [];
        // Search attachments
        $file->getFiles(
            path: '/Pages/' . $id . '/Attachments/*',
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

        // Block
        // Create block without format
        $block = new \App\Visualization\Block\Block([]);

        $block
            // Create object
            ->create()
                // Move inside
                ->jumpTo()
                // Define variables
                // data.title - Page name
                ->set('data.title', $data->get('data.custom.page_name'))
                // data.text - Page text
                ->set('data.text', $body = @file_get_contents(ROOT . '/Pages/' . $data->get('data.custom.page_id') . '/html.html'))
                // data.attachments - Array of attachments
                ->set('data.attachments', $attachments)
                // data.images - Array of images
                ->set('data.images', $images);

        // Finish block and ret ready for generate
        $data->block = $block->getDataToGenerate();

        // Set page title
        $data->set('data.head.title', $data->get('data.custom.page_name'));

        // Set page description
        $data->set('data.head.description', $body);
    }
}