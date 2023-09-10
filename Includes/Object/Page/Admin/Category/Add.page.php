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
		if (!in_array($system->get('site_mode'), ['forum', 'blog_with_forum']))
		{
            // Show 404 error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('forum')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Forum.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Category/Category.json');
        $form
            ->form('category')
                ->callOnSuccess($this, 'newCategory')
                ->frame('category')
                    ->title('L_CATEGORY.L_NEW');
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
    public function newCategory( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all categories one position up
        $db->moveOnePositionUp( table: TABLE_CATEGORIES );

        // Add category
        $db->insert(TABLE_CATEGORIES, [
            'category_name'         => $post->get('category_name'),
            'position_index'        => '1',
            'category_description'  => $post->get('category_description')
        ]);

        // Add permission
        $db->insert(TABLE_CATEGORIES_PERMISSION, [
            'category_id' => $db->lastInsertId()
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('category_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/forum/');
    }
}