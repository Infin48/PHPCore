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
 * Show
 */
class Show extends \App\Page\Page
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
		if ($system->get('site_mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('users')->elm2('group')->active();
        
        // Group
        $row = $db->select('app.group.get()', $this->url->getID()) or $this->error404();

        // Save group data
        $data->set('data.group', $row);

        if ($data->get('data.group.group_id') == 1)
        {
            if (LOGGED_USER_GROUP_ID != 1)
            {
                $this->error404();
            }
        } else
        {
            // If logged user doensn't have permisison to edit this group 
            if ($data->get('data.group.group_index') >= LOGGED_USER_GROUP_INDEX)
            {
                $this->error404();
            }
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Group.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.group.group_name'))->href('/admin/group/show/' . $data->get('data.group.group_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Group/Group.json');
        $form
            ->form('group')
                ->callOnSuccess($this, 'editGroup')
                ->data($data->get('data.group'))
                ->frame('group')
                    ->input('group_default', function ( \App\Visualization\Form\Form $form ) use ($data, $system)
                    {
                        if ($data->get('data.group.group_id') == 1)
                        {
                            $form->hide();
                            return;
                        }

                        if ($system->get('default_group') != $data->get('data.group.group_id'))
                        {
                            $form->show();
                        }
                    });
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
    public function editGroup( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // System
        $system = $data->get('inst.system');
        
        if ($post->get('group_default'))
        {       
            $db->query('
                UPDATE ' . TABLE_USERS . '
                SET group_id = ?
                WHERE group_id = ?
            ', [$data->get('data.group.group_id'), $system->get('default_group')]);

            $db->table(TABLE_SETTINGS, [
                'default_group' => $data->get('data.group.group_id')
            ]);
        }

        $db->update(TABLE_GROUPS, [
            'group_name'        => $post->get('group_name'),
            'group_color'       => $post->get('group_color'),
            'group_class'  => parse($post->get('group_name')) . $data->get('data.group.group_id')
        ], $data->get('data.group.group_id'));

        // Synchronize groups
        $groups = $db->select('app.group.all()');

        $css = '';
        foreach ($groups as $group)
        {
            $css .= '.username.user--' . $group['group_class'] . '{color:' . $group['group_color'] . '}.statue.statue--' . $group['group_class'] . '{background-color:' . $group['group_color'] . '}.group--' . $group['group_class'] . ' input[type="checkbox"] + label span{border-color:' . $group['group_color'] . '}.group--' . $group['group_class'] . ' input[type="checkbox"]:checked + label span{background-color:' . $group['group_color'] . '}';
        }
        file_put_contents(ROOT . '/Includes/Template/css/Group.min.css', $css);

        // Update group session
        $db->table(TABLE_SETTINGS, [
            'session_groups' => RAND
        ]);
        
        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('group_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/group/');
    }
}