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

namespace App\Page\Admin\Role;

/**
 * Add
 */
class Add extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.role';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');
        
        // If static mode is enabled or profiles are disabled
		if ($system->get('site.mode') == 'static' or $system->get('site.mode.blog.profiles') == 0)
		{
            // Show error page
			$this->error404();
		}

        // Navbar
        $this->navbar->elm1('users')->elm2('role')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Role.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Role.json');
        $form
            ->form('role')
                ->callOnSuccess($this, 'newRole')
                ->frame('role')
                    ->title('L_ROLE.L_NEW');
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
    public function newRole( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all roles one position up
        $db->moveOnePositionUp( table: TABLE_ROLES );

        // Add new role
        $db->insert(TABLE_ROLES, [
            'role_name'         => $post->get('role_name'),
            'role_color'        => $post->get('role_color'),
            'role_icon'         => $post->get('role_icon') ? 'fa-' . $post->get('role_icon_style') . ' fa-' . $post->get('role_icon') : '',
        ]);

        // Set role class
        $db->update(TABLE_ROLES, [
            'role_class' => parse($post->get('role_name')) . $db->lastInsertId()
        ], $db->lastInsertId());

        // Synchronize roles
        $roles = $db->select('app.role.all()');

        $css = '';
        foreach ($roles as $role)
        {
            $css .= '.role.role--' . $role['role_class'] . '{background-color:' . $role['role_color'] . '}label.role--' . $role['role_class'] . ' input[type="checkbox"] + label .checkbox-icon{border-color:' . $role['role_color'] . '}label.role.role--' . $role['role_class'] . ' input[type="checkbox"]:checked + label .checkbox-icon{background-color:' . $role['role_color'] . '}[js="title"].role--' . $role['role_class'] . '{background-color:' . $role['role_color'] . ';}[js="title"].role--' . $role['role_class'] . ' [js="title-arrow"]{border-top-color: ' . $role['role_color'] . ';}';
        }
        file_put_contents(ROOT . '/Includes/Template/css/Role.min.css', $css);

        // Update roles session
        $db->table(TABLE_SETTINGS, [
            'session.roles' => RAND
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('role_name'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/role/');
    }
}