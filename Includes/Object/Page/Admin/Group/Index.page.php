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
    protected string $permission = 'admin.group';
    
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
            'run/group/up' => 'moveGroupUp',
            'run/group/down' => 'moveGroupDown',
            'run/group/delete' => 'deleteGroup',

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
            'run/group/up',
            'run/group/down',
            'run/group/delete' => [
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

        // User
        $user = $data->get('inst.user');
        
        // If static mode is enabled
		if ($system->get('site.mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('users')->elm2('group')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Group.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Group/Index.json');
        $form->form('group')->callOnSuccess($this, 'newGroup');
        $data->form = $form->getDataToGenerate();

        // Groups
        $groups = $db->select('app.group.all()');

        // Save list of group's ids
        $data->set('data.groups', array_column($groups, 'group_id'));

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Group.json');
        $list->elm1('admin')->elm2('admin', function ( \App\Visualization\ListsAdmin\ListsAdmin $list ) use (&$groups)
        {
            $list
                ->set('data', array_merge(array_shift($groups), $list->get('data')))
                // Set links to buttons
                ->set('data.button.edit.href', '/admin/group/show/' . $list->get('data.group_id'));

            // If logged user is not administrator
            // Hide buton to edit group
            if (LOGGED_USER_GROUP_ID != 1)
            {
                $list->delete('data.button');
            }
        });

        $list->elm1('group')->fill(data: $groups, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count ) use ($groups, $user, $system)
        {
            $list
                ->set('data.html.ajax-id', $list->get('data.group_id'))
                // Set links to buttons
                ->set('data.button.edit.href', '/admin/group/show/' . $list->get('data.group_id'))
                ->set('data.button.permission.href', '/admin/group/permission/' .$list->get('data.group_id'));

            // If logged user has less group index than current group
            // Delete buttons and "disable" group
            if ($list->get('data.group_index') >= LOGGED_USER_GROUP_INDEX)
            {
                $list->disable()->delete('data.button');

                return;
            }

            $previous = $groups[$i - 2] ?? $groups[0];

            // Enable button to move group up on all groups except first
            if ($i !== 1)
            {
                // If previous group has less permission then
                // group from logged user
                // Allow user to move group up
                if ($previous['group_index'] < $user->get('group_index'))
                {
                    $list->enable('data.button.up');
                }
            }

            // Show button to delete group when current group is not default
            if ($list->get('data.group_id') != $system->get('default_group'))
            {
                $list->show('data.button.delete');
            }

            // Enable button to move group down on all groups except last
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
    public function newGroup( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $db->query('
            UPDATE ' . TABLE_GROUPS . '
            SET group_index = group_index + 1
        ');

        $db->insert(TABLE_GROUPS, [
            'group_name'        => $post->get('group_name'),
            'group_color'       => '#555555',
            'group_index'       => 1,
            'group_permission'  => ''
        ]);

        $db->update(TABLE_GROUPS, [
            'group_class' => parse($post->get('group_name')) . $db->lastInsertId()
        ], $db->lastInsertId());

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
            'session.groups' => RAND
        ]);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

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
    public function deleteGroup( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // System
        $system = $data->get('inst.system');

        if ($system->get('default_group') == $post->get('id'))
        {
            return false;
        }

        // Check if group exists
        if (!in_array($post->get('id'), $data->get('data.groups')))
        {
            return false;
        }

        $db->query('
            UPDATE ' . TABLE_GROUPS . '
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_index > g.group_index
            LEFT JOIN ' . TABLE_USERS . ' ON u.group_id = g.group_id
            SET g2.group_index = g2.group_index - 1,
                u.group_id = ?
            WHERE g.group_id = ?
        ', [$system->get('default_group'), $post->get('id')]);

        $categories = $db->query('
            SELECT *
            FROM ' . TABLE_CATEGORIES . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = c.category_id
        ', [], ROWS);

        foreach ($categories as $category)
        {
            $see = explode(',', $category['permission_see']);
            if (in_array($post->get('id'), $see)) {
                unset($see[array_search($post->get('id'), $see)]);
            }
            $db->update(TABLE_CATEGORIES_PERMISSION, [
                'permission_see' => implode(',', $see)
            ], $category['category_id']);

            $forums = $db->query('
                SELECT *
                FROM ' . TABLE_FORUMS . '
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
            ', [], ROWS);

            foreach ($forums as $forum)
            {
                $see = explode(',', $forum['permission_see']);
                if (in_array($post->get('id'), $see))
                {
                    unset($see[array_search($post->get('id'), $see)]);
                }
                $_post = explode(',', $forum['permission_post']);
                if (in_array($post->get('id'), $_post))
                {
                    unset($_post[array_search($post->get('id'), $_post)]);
                }
                $topic = explode(',', $forum['permission_topic']);
                if (in_array($post->get('id'), $topic)) {
                    unset($topic[array_search($post->get('id'), $topic)]);
                }
                $db->update(TABLE_FORUMS_PERMISSION, [
                    'permission_see' => implode(',', $see),
                    'permission_post' => implode(',', $_post),
                    'permission_topic' => implode(',', $topic)
                ], $forum['forum_id']);
            }
        }

        $db->query('
            DELETE g
            FROM ' . TABLE_GROUPS. ' 
            WHERE g.group_id = ?
        ', [$post->get('id')]);

        // Update groups session
        $db->table(TABLE_SETTINGS, [
            'session.groups' => RAND
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
    public function moveGroupUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if group exists
        if (!in_array($post->get('id'), $data->get('data.groups')))
        {
            return false;
        }

        // Move group one position up
        $db->query('
            UPDATE ' . TABLE_GROUPS . '
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_index = g.group_index + 1
            SET g.group_index = g.group_index + 1,
                g2.group_index = g2.group_index - 1
            WHERE g.group_id = ? AND g2.group_id IS NOT NULL AND g.group_index < ' . LOGGED_USER_GROUP_INDEX . ' AND g2.group_index < ' . LOGGED_USER_GROUP_INDEX
        , [$post->get('id')]);

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
    public function moveGroupDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if group exists
        if (!in_array($post->get('id'), $data->get('data.groups')))
        {
            return false;
        }

        $db->query('
            UPDATE ' . TABLE_GROUPS . '
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_index = g.group_index - 1
            SET g.group_index = g.group_index - 1,
                g2.group_index = g2.group_index + 1
            WHERE g.group_id = ? AND g2.group_id IS NOT NULL AND g.group_index < ' . LOGGED_USER_GROUP_INDEX . ' AND g2.group_index < ' . LOGGED_USER_GROUP_INDEX
        , [$post->get('id')]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }
}