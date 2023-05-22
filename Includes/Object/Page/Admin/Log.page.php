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

namespace App\Page\Admin;

/**
 * Log
 */
class Log extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.log';
    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');
        
        // If static mode is enabled
		if ($system->get('site.mode') == 'static')
		{
            // Show 404 error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('other')->elm2('log')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Log.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(20);
        $pagination->total($db->select('app.log.count()'));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Log.json');
        $list->elm1('log')->fill(data: $db->select('app.log.all()'));
        $data->list = $list->getDataToGenerate();
    }
}