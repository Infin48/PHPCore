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
 * Topic
 */
class Topic extends \App\Page\Page
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
		if ($system->get('site.mode') != 'forum')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('reported')->active()->elm3('topic')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Report/Topic.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Save statistics about reported contents
        $data->set('data.stats', $db->select('app.report.stats()'));

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_REPORTED_TOPIC);
        $pagination->total($data->get('data.stats.topic'));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Report/Topic.json');
        $list->elm1('topic')->fill(data: $db->select('app.report.topic()'), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list )
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
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Report/Topic.json');

        // Set number of reported topics
        $block->elm1('topic')->value($data->get('data.topic'));

        // Save block and get ready to generate
        $data->block = $block->getDataToGenerate();
    }
}