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
    protected string $permission = 'admin.role';
    
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
            'run/role/up' => 'moveRoleUp',
            'run/role/down' => 'moveRoleDown',
            'run/role/delete' => 'deleteRole',

            default => ''
        };
    }

    /**
     * Load data according to received ajax
     *
     * @param  string $ajax Received ajax
     * 
     * @return array Data
     */
    public function ajaxData( string $ajax )
    {
        return match($ajax)
        {
            'run/role/up',
            'run/role/down',
            'run/role/delete' => [
                'id' => STRING
            ],

            default => []
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
        
        // If static mode is enabled or profiles are disabled
		if ($system->get('site_mode') == 'static' or $system->get('site_mode_blog_profiles') == 0)
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('users')->elm2('role')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Role.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // List of roles
        $roles = $db->select('app.role.all()');

        // Save list of role's ids
        $data->set('data.roles', array_column($roles, 'role_id'));

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Role.json');
        $list->elm1('role')->fill(data: $roles, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count )
        {
            $list
                ->set('data.html.ajax-id', $list->get('data.role_id'))
                ->set('data.button.edit.href', '/admin/role/show/' . $list->get('data.role_id'));

            if ($i !== 1)
            {
                $list->enable('data.button.up');
            }

            if ($i !== $count)
            {
                $list->enable('data.button.down');
            }
        });
        $data->list = $list->getDataToGenerate();
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
    public function deleteRole( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if role exists
        if (!in_array($post->get('id'), $data->get('data.roles')))
        {
            return false;
        }

        // Move all previous roles one position down
        $db->query('
            UPDATE ' . TABLE_ROLES . '
            LEFT JOIN ' . TABLE_ROLES . '2 ON ro2.position_index > ro.position_index
            SET ro2.position_index = ro2.position_index - 1
            WHERE ro.role_id = ?
        ', [$post->get('id')]);

        // Delete role
        $db->delete(
            table: TABLE_ROLES,
            id: $post->get('id')
        );

        // Get users having this role
        $users = $db->query('
            SELECT user_roles, user_id
            FROM ' . TABLE_USERS . '
            WHERE FIND_IN_SET(?, u.user_roles)
        ', [$post->get('id')], ROWS);

        foreach ($users as $user)
        {
            $roles = explode(',', $user['user_roles']);
            unset($roles[array_search($post->get('id'), $roles)]);

            // Delete this role from user
            $db->update(TABLE_USERS, [
                'user_roles' => implode(',', $roles)
            ], $user['user_id']);
        }

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
    public function moveRoleUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if role exists
        if (!in_array($post->get('id'), $data->get('data.roles')))
        {
            return false;
        }

        // Move forum up
        $db->moveOnePositionUp( table: TABLE_ROLES, id: $post->get('id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
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
    public function moveRoleDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if role exists
        if (!in_array($post->get('id'), $data->get('data.roles')))
        {
            return false;
        }

        // Move forum down
        $db->moveOnePositionDown( table: TABLE_ROLES, id: $post->get('id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }
}