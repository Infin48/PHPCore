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

namespace App\Page\Admin\Forum;

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
        
        // Language
        $language = $data->get('inst.language');

        // If forum is not enabled
		if ($system->get('site_mode') != 'forum')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('forum')->active();

        // Get forum data from database
        $row = $db->select('app.forum.get()', $this->url->getID()) or $this->error404();

        // Save forum data
        $data->set('data.forum', $row);

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Forum.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.forum.forum_name'))->href('/admin/forum/permission/' . $data->get('data.forum.forum_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate(); 

        // All groups
        $groups = array_merge($db->select('app.group.all()'), [[
            'desc' => $language->get('L_FORUM.L_PERMISSION.L_EVERYBODY_DESC'),
            'group_id' => '*',
            'group_name' => $language->get('L_EVERYBODY'),
            'group_class' => 'visitor'
        ]]);

        // Forums
        $forums = $db->select('app.forum.withoutMainForum()', $this->url->getID());

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Forum/Permission.json');
        $form
            ->form('permission')
                ->data($data->get('data.forum'))
                ->callOnSuccess($this, 'editForumPermission')
                ->frame('groups')
                    ->fill(data: $groups)
                ->frame('inherit', function ( \App\Visualization\Form\Form $form ) use ($data)
                {
                    if (!$data->get('data.forum.forum_main'))
                    {
                        $form->show();
                    }
                })
                    ->input('inherit_id')->fill(data: $forums)
                    ->input('inherit_permission', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.forum.inherit_id'))
                        {
                            $form->elm4('yes')->check();
                            return;
                        }

                        $form->elm4('no')->check();
                    });
        $data->form = $form->getDataToGenerate();

        // Page title
        $data->set('data.head.title', $language->get('L_FORUM.L_FORUM') . ' - ' . $data->get('data.forum.forum_name'));
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
    public function editForumPermission( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $inheritId = null;
        $permissionSee = implode(',', $post->get('forum_permission_see') ?: []);
        $permissionPost = implode(',', $post->get('forum_permission_post') ?: []);
        $permissionTopic = implode(',', $post->get('forum_permission_topic') ?: []);

        // Forum is not main
        if (!$data->get('data.forum.forum_main'))
        {
            // If permissions will be inherited
            if ($post->get('inherit_permission') == true)
            {
                $inheritId = $post->get('inherit_id');
                if ($inheritId)
                {
                    // Get forum from which permissions will be inherited
                    $forum = $db->select('app.forum.get()', $post->get('inherit_id'));

                    $permissionSee = implode(',', $forum['permission_see_forum']);
                    $permissionPost = implode(',', $forum['permission_post']);
                    $permissionTopic = implode(',', $forum['permission_topic']);
                }
            }

            // Get forums which inherit permisson from this forum
            $forums = $db->query('
                SELECT *
                FROM ' . TABLE_FORUMS_PERMISSION . '
                WHERE fp.inherit_id = ?
            ', [$data->get('data.forum.forum_id')], ROWS);

            foreach ($forums as $forum)
            {
                // Update forum permission
                $db->update(TABLE_FORUMS_PERMISSION, [
                    'permission_see' => $permissionSee,
                    'permission_post' => $permissionPost,
                    'permission_topic' => $permissionTopic
                ], $forum['forum_id']);
            }
        }

        $perm = [
            'inherit_id' => $inheritId,
            'permission_see' => $permissionSee,
            'permission_post' => $permissionPost,
            'permission_topic' => $permissionTopic
        ];

        // Forum is main
        if ($data->get('data.forum.forum_main'))
        {
            $perm['permission_see'] = '*';
        }

        // Update forum permission
        $db->update(TABLE_FORUMS_PERMISSION, $perm, $data->get('data.forum.forum_id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.forum.forum_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/forum/');
    }
}