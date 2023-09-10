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

namespace App\Page\Admin\Report;

/**
 * Post
 */
class Post extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.forum';
    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');
        
        // If forum is not enabled
		if (!in_array($system->get('site_mode'), ['forum', 'blog_with_forum']))
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('reported')->active()->elm3('post')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Report/Post.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();
        
        // Save statistics about reported contents
        $data->set('data.stats', $db->select('app.report.stats()'));

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_REPORTED_POST);
        $pagination->total($data->get('data.stats.post'));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Report/Post.json');

        // Fill list with reported contents
        $list->elm1('post')->fill(data: $db->select('app.report.post()'), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list )
        {
            // If report is not closed
            if ($list->get('data.report_status') == 0)
            {
                // Show label
                $list->addLabel(
                    color: 'red',
                    icon: 'fa-solid fa-exclamation'
                );
            }
        });

        // Save list and get ready to generate
        $data->list = $list->getDataToGenerate();

        // Block
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Report/Post.json');

        // Set number of reported posts
        $block->elm1('post')->value($data->get('data.post'));

        // Save block and get ready to generate
        $data->block = $block->getDataToGenerate();
    }
}