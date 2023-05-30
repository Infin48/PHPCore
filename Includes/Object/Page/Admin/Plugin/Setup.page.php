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

namespace App\Page\Admin\Plugin;

/**
 * Setup
 */
class Setup extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Plugin.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.plugin';
    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // Navbar
        $this->navbar->elm1('settings')->elm2('plugin')->active();
        
        // File
        $file = new \App\Model\File\File();

        // Language
        $language = $data->get('inst.language');
        
        // Plugin
        $plugin = $data->get('inst.plugin');

        // Load plugin
        $plugin = $plugin->findByID($this->url->getID());

        // If plugin is not installed
        if (!$plugin->isInstalled())
        {
            // Show error page
            $this->error404();
        }

        // Save plugin data
        $data->set('data.plugin', $plugin->get());
        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Plugin.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.plugin.name'))->href('/admin/plugin/setup/' . $data->get('data.plugin.id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Build page class
        $page = $this->buildPage(
            path: '/Plugins/' .  $data->get('data.plugin.folder') . '/Object/Page/Admin',
            object: 'Plugin\\' . $data->get('data.plugin.folder') . '\Page\Admin'
        );

        if (!$page or get_class($page) === 'Plugin\\' . $data->get('data.plugin.folder') . '\Page\Admin\Index') 
        {   
            // Block
            $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Plugin.json');
            $block
                ->elm1('name')
                    ->value($data->get('data.plugin.name'))
                ->elm1('version')
                    ->value($data->get('data.plugin.version.version'))
                ->elm1('author')
                    ->value($data->get('data.plugin.author.name') ?: '?')
                    ->href($data->get('data.plugin.author.link'))
                ->elm1('id')->value($data->get('data.plugin.id'));
            $data->block = $block->getDataToGenerate();

            $settings = [
                'plugin_language' => $data->get('data.plugin.language'),
                'plugin_template' => $data->get('data.plugin.template')
            ];

            // Form
            $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Plugin.json');
            $form->data($settings);
            $form
                ->form('plugin')
                ->data($settings)
                ->callOnSuccess($this, 'editPluginSettings')
                    ->frame('info', function( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.plugin.info'))
                        {
                            $form->show()->input('info')->value($data->get('data.plugin.info'));
                        }
                    })
                    ->frame('plugin')
                        ->input('show', function( \App\Visualization\Form\Form $form ) use ($data, $file)
                        {
                            if ($file->exists('/Plugins/' . $data->get('data.plugin.folder') . '/Object/Page/Plugin/Index.page.php'))
                            {
                                $form->show()->set('data.href', $this->url->build('/plugin/' . $this->url->getID() . '/'));
                            }
                        });

            $listLanguages = [];
            // Loads plugin languages from folder
            $file->getFiles(
                path: '/Plugins/' . $data->get('data.plugin.folder') . '/Languages/*',
                flag: \App\Model\File\File::DO_NOT_NEST,
                function: function ( \App\Model\File\File $file, string $path ) use (&$listLanguages){
                    
                    $listLanguages[] = [
                        'label' => basename($path),
                        'value' => basename($path)
                    ];
                }
            );
            $form->input('plugin_language')->fill($listLanguages);
            
            $listTemplates = [];
            // Loads plugin templates from folder
            $file->getFiles(
                path: '/Plugins/' . $data->get('data.plugin.folder') . '/Styles/*',
                flag: \App\Model\File\File::DO_NOT_NEST,
                function: function ( \App\Model\File\File $file, string $path ) use (&$listTemplates){
                    
                    $listTemplates[] = [
                        'label' => basename($path),
                        'value' => basename($path)
                    ];
                }
            );
            $form->input('plugin_template')->fill($listTemplates);
            $data->form = $form->getDataToGenerate();
        }

        if ($page)
        {
            $page->navbar = $this->navbar;
            
            // Run plugin page
            $page->body( data: $data, db: $db );

            // Check for ajax
            $page->checkForAjax();
        }

        // Set page title
        $data->set('data.head.title', $language->get('L_PLUGIN.L_PLUGIN') . ' - ' . $data->get('data.plugin.folder'));
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
    public function editPluginSettings( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $db->update(TABLE_PLUGINS, [
            'plugin_template' => $post->get('plugin_template'),
            'plugin_language' => $post->get('plugin_language')
        ], $data->get('data.plugin.id'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
    }
}