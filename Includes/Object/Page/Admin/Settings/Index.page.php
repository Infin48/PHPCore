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

namespace App\Page\Admin\Settings;

use \App\Model\File\File;

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
    protected string $permission = 'admin.settings';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');
        
        // Navbar
        $this->navbar->elm1('settings')->elm2('settings')->active()->elm3('site')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Settings/Settings.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // File
        $file = new File();

        // List
        $list = [
            ['label' => 'en', 'value' => 'en']
        ];

        // Loads editor languages from folder
        $file->getFiles(
            path: '/Assets/Trumbowyg/langs/*.min.js',
            flag: File::REMOVE_EXTENSION|File::SKIP_FOLDERS,
            function: function ( \App\Model\File\File $file, string $path ) use (&$list){

                $list[] = [
                    'label' => basename($path),
                    'value' => basename($path)
                ];
            }
        );

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Settings/Index.json');
        $form
            ->form('settings')
                ->callOnSuccess($this, 'editWebsiteSettings')
                ->data($system->get())
                ->frame('language')
                    ->input('site_language_editor')
                        ->fill(data: $list)
                ->frame('settings')
                    ->input('site_mode_static_index')
                        ->fill(data: $db->select('app.page.all()'))
                    ->input('delete_site_favicon', function ( \App\Visualization\Form\Form $form ) use ($system)
                    {
                        if ($system->get('site_favicon'))
                        {
                            $form->show();
                        }
                    });
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
    public function editWebsiteSettings( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $mode = $post->get('site_mode');
        if ($mode == 'static')
        {
            if (!$post->get('site_mode_static_index'))
            {
                // System
                $system = $data->get('inst.system');

                $mode = $system->get('site_mode');
            }
        }

        // System settings
        $settings = [
            'site_name' => $post->get('site_name'),
            'site_locale' => $post->get('site_locale'),
            'site_timezone' => $post->get('site_timezone'),
            'site_keywords' => $post->get('site_keywords'),
            'site_description' => $post->get('site_description'),
            'site_language_editor' => $post->get('site_language_editor'),

            'site_mode' => $mode,
            'site_mode_forum_index' => $mode == 'forum' ? $post->get('site_mode_forum_index') : 1,
            'site_mode_static_index' => $post->get('site_mode_static_index'),
            'site_mode_blog_profiles' => $mode == 'blog' ? (int)$post->get('site_mode_blog_profiles') : 1,
            'site_mode_blog_editing' => $mode == 'blog' ? (int)$post->get('site_mode_blog_editing') : 0,
			
			'image_gif' => (int)$post->get('image_gif'),
            'image_max_size' => (int)$post->get('image_max_size'),

            'cookie_text' => $post->get('cookie_text'),
            'cookie_enabled' => (int)$post->get('cookie_enabled')
        ];

        if ($mode == 'blog')
        {
            $settings['registration_enabled'] = 0;
        }

        // Fiele model
        $file = new \App\Model\File\File();

        // Load background image
        $favicon = $post->get('site_favicon');

        // Ignore max image limit
        $favicon->ignoreLimit();
        
        // If was favicon uploaded
        if ($favicon->exists())
        {
            // Upload image
            $favicon->upload('/Uploads/Site', 'Favicon');

            $settings['site_favicon'] = $favicon->getFormat();
        }

        // If is checked "delete site favicon"
        if ($post->get('delete_site_favicon'))
        {
            // Delete image
            $file->delete('/Uploads/Site/Favicon.*');

            $settings['site_favicon'] = '';
        }

        // Update settings
        $db->table(TABLE_SETTINGS, $settings);

        // Update sessions
        $db->table(TABLE_SETTINGS, [
            'session' => RAND,
            'session_scripts' => RAND
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
    }
}