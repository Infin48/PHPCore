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
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Report.phtml';

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
        $this->navbar->elm1('forum')->elm2('reported')->active()->elm3('index')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Report/Report.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Report/Index.json');

        $list
            // Fill list with last unclosed reports 
            ->elm1('last')->fill(data: $db->select('app.report.pending()'))
            // Fill list with users with the number of most reported content
            ->elm1('users')->fill(data: $db->select('app.report.user()'))
            // Fill list with list of solved reports
            ->elm1('solved')->fill(data: $db->select('app.report.solved()'));

        // Split list
        $list->split(1, 1, 1);

        // Save list and get ready to generate
        $data->list = $list->getDataToGenerate();

        // Save stats data and unite with others
        $data->set('data.stats', $db->select('app.report.stats()'));

        // Block
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Report/Index.json');
        $block
            // Set number of reported posts
            ->elm1('post')->value($data->get('data.stats.post'))
            // Set number of reported topics
            ->elm1('topic')->value($data->get('data.stats.topic'))
            // Set number of reported profile posts
            ->elm1('profile_post')->value($data->get('data.stats.profile_post'))
            // Set number of reported comments under profile posts
            ->elm1('profile_post_comment')->value($data->get('data.stats.profile_post_comment'));

        // Save block and get ready to generate
        $data->block = $block->getDataToGenerate();
    }
}