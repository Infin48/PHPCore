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
		if ($system->get('site_mode') != 'forum')
		{
            // Show 404 error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('forum')->active();

        // Get category data from database
        $row = $db->select('app.category.get()', $this->url->getID()) or $this->error404();
        
        // Save category data and unite with others
        $data->set('data.category', $row);

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Forum.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.category.category_name'))->href('/admin/category/show/' . $data->get('data.category.category_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();
        
        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Category/Category.json');
        $form
            ->form('category')
                ->callOnSuccess($this, 'editCategory')
                ->data($data->get('data.category'))
                ->frame('category')
                    ->title('L_CATEGORY.L_EDIT');
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
    public function editCategory( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Update category
        $db->update(TABLE_CATEGORIES, [
            'category_name'         => $post->get('category_name'),
            'category_description'  => $post->get('category_description')
        ], $data->get('data.category.category_id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('category_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/forum/');
    }
}