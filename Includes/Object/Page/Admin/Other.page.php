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
 * Other
 */
class Other extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.?';
    
    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    protected function ajax( string $ajax )
    {
        return match($ajax)
        {
            'run/optimize-tables' => 'optimizeTables',
            
            'run/synchronize-roles' => 'synchronizeRoles',
            'run/synchronize-groups' => 'synchronizeGroups',
            'run/synchronize-labels' => 'synchronizeLabels',
            'run/synchronize-styles' => 'synchronizeStyles',
            'run/synchronize-scripts' => 'synchronizeScripts',
            'run/synchronize-template' => 'synchronizeTemplate',

            default => ''
        };
    }

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
		if ($system->get('site_mode') == 'static')
		{
            // Show 404 error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('other')->elm2('other')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Other.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Other.json');
        $form
            ->form('other')
                ->disButtons();
        $data->form = $form->getDataToGenerate();
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
    public function synchronizeGroups( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
       // Groups
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
       $db->addToLog( name: __FUNCTION__ );

       // Show success message
       $data->set('data.message.success', __FUNCTION__);

       // Refresh page
       $data->set('options.refresh', true);
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
    public function synchronizeLabels( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
       // Labels
       $labels = $db->select('app.label.all()');

       $css = '';
       foreach ($labels as $label)
       {
           $css .= '.label.label--' . $label['label_class'] . '{background-color:' . $label['label_color'] . '}.label-checkbox.label--' . $label['label_class'] . '{color:' . $label['label_color'] . ' !important}.label--' . $label['label_class'] . ' input[type="checkbox"] + label .checkbox-icon{border-color:' . $label['label_color'] . '}.label--' . $label['label_class'] . ' input[type="checkbox"]:checked + label .checkbox-icon{background-color:' . $label['label_color'] . '}';
       }
       file_put_contents(ROOT . '/Includes/Template/css/Label.min.css', $css);

       // Update labels session
       $db->table(TABLE_SETTINGS, [
           'session_labels' => RAND
       ]);

       // Add record to log
       $db->addToLog( name: __FUNCTION__ );

       // Show success message
       $data->set('data.message.success', __FUNCTION__);

       // Refresh page
       $data->set('options.refresh', true);
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
    public function synchronizeRoles( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
       // Roles
       $roles = $db->select('app.role.all()');

       $css = '';
       foreach ($roles as $role)
       {
           $css .= '.role.role--' . $role['role_class'] . '{background-color:' . $role['role_color'] . '}label.role--' . $role['role_class'] . ' input[type="checkbox"] + label .checkbox-icon{border-color:' . $role['role_color'] . '}label.role.role--' . $role['role_class'] . ' input[type="checkbox"]:checked + label .checkbox-icon{background-color:' . $role['role_color'] . '}[js="title"].role--' . $role['role_class'] . '{background-color:' . $role['role_color'] . ';}[js="title"].role--' . $role['role_class'] . ' [js="title-arrow"]{border-top-color: ' . $role['role_color'] . ';}';
       }
       file_put_contents(ROOT . '/Includes/Template/css/Role.min.css', $css);

       // Update roles session
       $db->table(TABLE_SETTINGS, [
           'session_roles' => RAND
       ]);

       // Add record to log
       $db->addToLog( name: __FUNCTION__ );

       // Show success message
       $data->set('data.message.success', __FUNCTION__);

       // Refresh page
       $data->set('options.refresh', true);
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
    public function synchronizeStyles( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Update styles session
        $db->table(TABLE_SETTINGS, [
            'session_styles' => RAND
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Refresh page
        $data->set('options.refresh', true);
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
    public function synchronizeScripts( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Update scripts session
        $db->table(TABLE_SETTINGS, [
            'session_scripts' => RAND
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
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
    public function synchronizeTemplate( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Update template session
        $db->table(TABLE_SETTINGS, [
            'session_template' => RAND
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
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
    public function optimizeTables( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get tables to optimize
        $result = $db->query('
            SHOW TABLE STATUS WHERE Data_free > 0
        ', [], ROWS);

        foreach ($result as $table)
        {
            $db->query('OPTIMIZE TABLE `' . $table['Name'] . '`');
        }

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
    }
}