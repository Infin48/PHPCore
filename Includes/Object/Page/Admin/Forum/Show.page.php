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
		if ($system->get('site.mode') != 'forum')
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

        if ($data->get('data.forum.forum_icon'))
        {
            $ex = explode(' ', str_replace('fa-', '', $data->get('data.forum.forum_icon')));
            $data->set('data.forum.forum_icon', $ex[1]);
            $data->set('data.forum.forum_icon_style', $ex[0]);
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Forum.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.forum.forum_name'))->href('/admin/forum/show/' . $data->get('data.forum.forum_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Categories
        $categories = $db->select('app.category.all()');
        
        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Forum/Forum.json');
        $form
            ->form('forum')
                ->callOnSuccess($this, 'editForum')
                ->data($data->get('data.forum'))
                ->frame('forum')
                    ->title('L_FORUM.L_EDIT')
                    ->input('category_id')
                        ->show()
                        ->fill(data: $categories)
                    ->input('enable_link', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.forum.forum_link'))
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
    public function editForum( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // If was changed category
        if ($post->get('category_id') != $post->get('category_id'))
        {
            // Change category
            $db->query('
                UPDATE ' . TABLE_FORUMS . '
                LEFT JOIN ' . TABLE_FORUMS . '2 ON f2.category_id = ?
                LEFT JOIN ' . TABLE_FORUMS . '3 ON f3.category_id = f.category_id AND f3.position_index > f.position_index
                SET f.category_id = ?,
                    f.position_index = 1,
                    f2.position_index = f2.position_index + 1,
                    f3.position_index = f3.position_index - 1
                WHERE f.forum_id = ?
            ', [$post->get('category_id'), $post->get('category_id'), $data->get('data.forum.forum_id')]);
        }
        
        $isMain = $post->get('forum_main');
        if ($post->get('enable_link'))
        {
            $isMain = 0;
        } 

        // If was checked "select forum as main"
        if ($isMain)
        {
            // Set all other forums as not-main
            $db->update(TABLE_FORUMS, ['forum_main' => '0']);

            // Update forum permissions
            $db->query('UPDATE ' . TABLE_FORUMS_PERMISSION . ' SET inherit_id = NULL WHERE inherit_id = ?', [$data->get('data.forum.forum_id')]);

            // Update forum permission
            $db->update(TABLE_FORUMS_PERMISSION, [
                'inherit_id' => null,
                'permission_see' => '*'
            ], $data->get('data.forum.forum_id'));

            // Update category permission
            $db->update(TABLE_CATEGORIES_PERMISSION, [
                'permission_see' => '*'
            ], $data->get('data.forum.category_id'));
        }
        
        // Update forum
        $db->update(TABLE_FORUMS, [
            'forum_main'        => $isMain,
            'forum_link'        => $post->get('enable_link') ? $post->get('forum_link') : '',
            'forum_url'         => parse($post->get('forum_name')),
            'forum_name'        => $post->get('forum_name'),
            'forum_icon'        => $post->get('forum_icon') ? 'fa-' . $post->get('forum_icon_style') . ' fa-' . $post->get('forum_icon') : '',
            'forum_description' => $post->get('forum_description')
        ], $data->get('data.forum.forum_id'));
        
        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('forum_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/forum/');
    }
}