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

namespace App\Page\Admin\Category;

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
            // Show 404 error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('forum')->active();
        
        // Get category data from database
        $row = $db->select('app.category.get()', $this->url->getID()) or $this->error404();

        // Save category data
        $data->set('data.category', $row);

        // If any forum in this category is set as main(default)
        if ($data->get('data.category.forum_main'))
        {
            // Show 404 error page
            $this->error404();
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Forum.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.category.category_name'))->href('/admin/category/permission/' . $data->get('data.category.category_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate(); 

        // All groups
        $groups = array_merge($db->select('app.group.all()'), [[
            'desc' => $language->get('L_FORUM.L_PERMISSION.L_EVERYBODY_DESC'),
            'group_id' => '*',
            'group_name' => $language->get('L_EVERYBODY'),
            'group_class' => 'visitor'
        ]]);

        // Categories
        $categories = $db->select('app.category.withoutMainForum()', $this->url->getID());

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Category/Permission.json');
        $form
            ->form('permission')
                ->callOnSuccess($this, 'editCategoryPermission')
                ->data($data->get('data.category'))
                ->frame('groups')->fill($groups)
                ->frame('inherit')
                    ->input('inherit_id')->fill($categories)
                    ->input('inherit_permission', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.category.inherit_id'))
                        {
                            $form->elm4('yes')->check();
                            return;
                        }

                        $form->elm4('no')->check();
                    });
        $data->form = $form->getDataToGenerate();

        // Set page title
        $data->set('data.head.title', $language->get('L_CATEGORY.L_CATEGORY') . ' - ' . $data->get('data.category.category_name'));
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
    public function editCategoryPermission( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $inheritId = null;
        $permissionSee = $post->get('category_permission_see') ?: [];

        // If permissions will be inherited
        if ($post->get('inherit_permission') == true)
        {
            $inheritId = $post->get('inherit_id');
            if ($inheritId)
            {
                $category = $db->select('app.category.get()', $inheritId);

                $permissionSee = $category['permission_see'];
            }
        }

        // Get categories which inherit permisson from this category
        $categories = $db->query('
            SELECT *
            FROM ' . TABLE_CATEGORIES_PERMISSION . '
            WHERE cp.inherit_id = ?
        ', [$data->get('data.category.category_id')], ROWS);

        foreach ($categories as $category)
        {
            // Update category permission
            $db->update(TABLE_CATEGORIES_PERMISSION, [
                'permission_see' => implode(',', $permissionSee),
            ], $category['category_id']);
        }

        // Update category permission
        $db->update(TABLE_CATEGORIES_PERMISSION, [
            'inherit_id' => $inheritId,
            'permission_see' => implode(',', $permissionSee)
        ], $data->get('data.category.category_id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.category.category_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/forum/');
    }
}