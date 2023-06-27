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

namespace App\Page\Admin\Deleted;

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
		if ($system->get('site_mode') != 'forum')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('deleted')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Deleted.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Block

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(20);
        $pagination->total($db->select('app.deleted.count()'));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Deleted.json');

        // Fill list with deleted content
        $list->elm1('deleted')->fill(data: $db->select('app.deleted.all()'), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list )
        {
            $list->set('data.id', $list->get('data.deleted_id'));
        });

        // Save list and get ready to generate
        $data->list = $list->getDataToGenerate();

        // Save stats data and unite with others
        $data->set('data.stats', $db->select('app.deleted.stats()'));

        // Block
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Deleted/Index.json');
        $block
            // Set number of deleted posts
            ->elm1('post')->value($data->get('data.stats.post_deleted'))
            // Set number of deleted topics
            ->elm1('topic')->value($data->get('data.stats.topic_deleted'))
            // Set number of deleted profile posts
            ->elm1('profile_post')->value($data->get('data.stats.profile_post_deleted'))
            // Set number of deleted comments under profile posts
            ->elm1('profile_post_comment')->value($data->get('data.stats.profile_post_comment_deleted'));

        // Save block and get ready to generate
        $data->block = $block->getDataToGenerate();
    }
}