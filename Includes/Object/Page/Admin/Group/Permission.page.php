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

namespace App\Page\Admin\Group;

/**
 * Permission
 */
class Permission extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;
    
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.group';
    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // Language
        $language = $data->get('inst.language');
        
        // If static mode is enabled
		if ($system->get('site.mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('users')->elm2('group')->active();

        // Get group data from database
        $row = $db->select('app.group.get()', $this->url->getID()) or $this->error404();

        // Save group data
        $data->set('data.group', $row);

        // If this group is administrator group
        if ($data->get('data.group.group_id') == 1)
        {
            // Show error page
            $this->error404();
        }

        // If logged user doensn't have permisison to edit this group 
        if ($data->get('data.group.group_index') >= LOGGED_USER_GROUP_INDEX)
        {
            $this->error404();
        }

        // Data to template
        $data->set('group_permission', $data->get('data.group.group_permission'));

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Group.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.group.group_name'))->href('/admin/group/show/' . $data->get('data.group.group_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Group/Permission.json');
        $form
            ->form('permission')
                ->callOnSuccess($this, 'editGroupPermission')
                ->data($data->get('data.group'));
        $data->form = $form->getDataToGenerate();

        // Page title
        $data->set('data.head.title', $language->get('L_GROUP.L_GROUP') . ' - ' . $data->get('data.group.group_name'));
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
    public function editGroupPermission( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $db->update(TABLE_GROUPS, [
            'group_permission'  => implode(',', $post->get('group_permission') ?: [])
        ], $data->get('data.group.group_id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.group.group_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/group/');
    }
}