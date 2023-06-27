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

namespace App\Page\Admin\User;

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
    protected string $permission = 'admin.user';

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

        // If static mode is enabled
		if ($system->get('site_mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('users')->elm2('user')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/User.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();
        
        $roles = [];
        if ($permission->has('admin.role'))
        {
            // Roles
            $roles = $db->select('app.role.all()');
        }

        // Groups
        $groups = $db->select('app.group.less()');

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/User/User.json');
        $form
            ->form('user')
                ->callOnSuccess($this, 'newUser')
                ->frame('image')
                    ->hide()
                ->frame('user')
                    ->input('group_id')->show()->fill($groups)
                    ->input('user_password')->require()
                    ->input('user_roles', function ( \App\Visualization\Form\Form $form ) use ($roles, $system)
                    {
                        // Logged user has permission to edit roles
                        if ($system->get('site_mode') != 'blog')
                        {
                            $form->show()->fill($roles);
                        }
                    });
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
    public function newUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // If exists user with entered user name
        if ($db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_name = ?', [$post->get('user_name')]))
        {
            throw new \App\Exception\Notice('user_name_exist');
        }

        // If exists user with entered e-mail
        if ($db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_email = ?', [$post->get('user_email')]))
        {
            throw new \App\Exception\Notice('user_email_exist');
        }

        $check = new \App\Model\Check();

        // Check password
        if ($check->password($post->get('user_password')))
        {
            $password = password_hash($post->get('user_password'), PASSWORD_DEFAULT);
        }

        // Check e-mail
        if (!$check->email($post->get('user_email')))
        {
            return false;
        }

        // Add user
        $db->insert(TABLE_USERS, [
            'group_id'              => $post->get('group_id'),
            'user_name'             => $post->get('user_name'),
            'user_email'            => $post->get('user_email'),
            'user_roles'            => implode(',', $post->get('user_roles') ?: []),
            'user_password'         => $password,
            'user_profile_image'    => getProfileImageColor()
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('user_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect
        $data->set('data.redirect', '/admin/user/');
    }
}