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

namespace App\Page\Admin;

/**
 * Sidebar
 */
class Sidebar extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.sidebar';
    
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
            'run/sidebar-object/up' => 'moveSidebarObjectUp',
            'run/sidebar-object/down' => 'moveSidebarObjectDown',
            'run/sidebar-object/create' => 'newSidebarObject',
            'run/sidebar-object/delete' => 'deleteSidebarObject',

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
            'run/role/create',
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

        // Language
        $language = $data->get('inst.language');
        
        // If static mode is enabled
		if ($system->get('site_mode') == 'static')
		{
            // Show 404 error page
			$this->error404();
		}

        // Navbar
        $this->navbar->elm1('appearance')->elm2('sidebar')->active();

        // File
        $file = new \App\Model\File\File();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Sidebar.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Get list of installed sidebar objects from database
        $sidebars = $db->select('app.sidebar.all()');

        // From this list get only ther object names
        $values = array_column($sidebars, 'sidebar_object');

        $pluginSidebars = [];

        // List of files from which objects will be loaded
        $files = ['/Includes/Object/Visualization/Sidebar/Formats/Basic.json'];

        // Foreach every installed plugin
        foreach (LIST_OF_INSTALLED_PLUGINS as $plugin)
        {
            // If plugin has file for sidebar format
            if ($file->exists('/Plugins/' . $plugin . '/Object/Visualization/Sidebar/Formats/Basic.json'))
            {
                // Add this file to list of files
                array_push($files, '/Plugins/' . $plugin . '/Object/Visualization/Sidebar/Formats/Basic.json');
            }
        }

        // Foreach every file
        foreach ($files as $file)
        {
            // Load sidebar from file
            $sidebar = new \App\Visualization\Visualization($file, false);

            // Foreach throught first elements
            foreach (array_keys($sidebar->get('body')) as $object)
            {
                // If this object is already installed
                if (in_array($object, $values))
                {
                    continue;
                }

                // Add this object to list
                array_push($pluginSidebars, [
                    'sidebar_object' => $object,
                    'sidebar' => $object
                ]);
            }
        }    

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Sidebar.json');
        $form
            ->form('sidebar')
                ->callOnSuccess($this, 'newSidebarObject');

        // If list with available objects is empty
        if (!$pluginSidebars)
        {
            // Remove buttons from form
            $form->disButtons();
        }

        // Fill form with loaded objects
        $form->frame('sidebar')->input('sidebar_object')->fill(data: $pluginSidebars);

        // Save form and get ready to generate
        $data->form = $form->getDataToGenerate();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Sidebar.json');

        // Fill list with sidebar objects
        $list->elm1('sidebar')->fill(data: $sidebars, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count ) use ($language)
        {
            $list
                ->set('data.title', $list->get('data.sidebar_object'))
                ->set('data.html.ajax-id', $list->get('data.sidebar_id'));

            // If object belongs to default
            if (in_array($list->get('data.title'), ['onlineusers', 'posts', 'profileposts', 'stats']))
            {
                // Add description
                $list->set('data.desc', $language->get('L_ONLY_ON_DISABLED_BLOG_MODE'));
            }

            // If this is not first object
            if ($i !== 1)
            {
                // Show move up button
                $list->enable('data.button.up');
            }

            // If this is not last object
            if ($i !== $count)
            {
                // Show move down button
                $list->enable('data.button.down');
            }
        });

        // Save list and get ready to generate
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
    public function newSidebarObject( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all sidebar elements one position up
        $db->moveOnePositionUp(TABLE_SIDEBAR);
        
        // Add sidebar
        $db->insert(TABLE_SIDEBAR, [
            'sidebar_object' => $post->get('sidebar_object')
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
    public function deleteSidebarObject( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all previous sidebar objects one position down
        $db->query('
            UPDATE ' . TABLE_SIDEBAR . '
            LEFT JOIN ' . TABLE_SIDEBAR . '2 ON s2.position_index > s.position_index
            SET s2.position_index = s2.position_index - 1
            WHERE s.sidebar_id = ?
        ', [$post->get('id')]);

        // Delete sidebar object
        $db->delete(
            table: TABLE_SIDEBAR,
            id: $post->get('id')
        );

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
    public function moveSidebarObjectUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move sidebar element up
        $db->moveOnePositionUp( table: TABLE_SIDEBAR, id: $post->get('id'));

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
    public function moveSidebarObjectDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move sidebar element down
        $db->moveOnePositionDown( table: TABLE_SIDEBAR, id: $post->get('id') );

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }
}