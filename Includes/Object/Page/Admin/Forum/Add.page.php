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
 * Add
 */
class Add extends \App\Page\Page
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
        
        // If forum is not enabled
		if ($system->get('site_mode') != 'forum')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('forum')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Forum.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Forum/Forum.json');
        $form
            ->form('forum')
                ->callOnSuccess($this, 'newForum')
                ->frame('forum')
                    ->title('L_FORUM.L_NEW')
                    ->input('enable_link')
                        ->elm4('no')->check();
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
    public function newForum( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all forums from category one position up
        $db->query('UPDATE ' . TABLE_FORUMS . ' SET position_index = position_index + 1 WHERE category_id = ?', [$this->url->getID()]);

        $isMain = $post->get('forum_main');
        if ($post->get('enable_link'))
        {
            $isMain = 0;
        } 

        // If was checked "select forum as main"
        if ($post->get('forum_main'))
        {
            // Set all other forums as not-main
            $db->update(TABLE_FORUMS, ['forum_main' => '0']);
        }

        // Add forum
        $db->insert(TABLE_FORUMS, [
            'forum_main'        => $isMain,
            'forum_url'         => parse($post->get('forum_name')),
            'forum_link'        => $post->get('forum_link'),
            'forum_name'        => $post->get('forum_name'),
            'forum_icon'        => $post->get('forum_icon') ? 'fa-' . $post->get('forum_icon_style') . ' fa-' . $post->get('forum_icon') : '',
            'category_id'       => $this->url->getID(),
            'forum_description' => $post->get('forum_description')
        ]);

        // Add permission
        $db->insert(TABLE_FORUMS_PERMISSION, [
            'forum_id' => $db->lastInsertId(),
            'permission_see' => $isMain ? '*' : ''
        ]);

        if ($isMain)
        {
            // Update category permission
            $db->update(TABLE_CATEGORIES_PERMISSION, [
                'permission_see' => '*'
            ], $this->url->getID());
        }

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('forum_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/forum/');
    }
}