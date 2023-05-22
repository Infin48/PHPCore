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

/**
 * Language
 */
class Language extends \App\Page\Page
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
            'run/language/delete' => 'deleteLanguage',
            'run/language/activate' => 'activateLanguage',

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
            'run/language/delete',
            'run/language/activate' => [
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

        // Navbar
        $this->navbar->elm1('settings')->elm2('settings')->active()->elm3('language')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Settings/Language.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Notification
        $notification = new \App\Visualization\Notification\Notification($data->notification);
        $notification
            // Create new object(notification) and jump inside
            ->create()->jumpTo()
            // Set name
            ->set('data.name', 'plugins')
            // Set type
            ->set('data.type', 'info')
            // Set title
            ->set('data.title', 'L_NOTIFI.L_LANGUAGE.L_TITLE')
            // Set title
            ->set('data.text', 'L_NOTIFI.L_LANGUAGE.L_DESC')
                // set icon to button
                ->set('data.button.download.icon', 'fa-solid fa-download')
                // Set text to button
                ->set('data.button.download.text', 'L_BTN.L_AVAILABLE_LANGUAGES')
                // Set link to button
                ->set('data.button.download.href', 'http://phpcore.cz/doplnky/')
                // set icon to button
                ->set('data.button.doc.icon', 'fa-solid fa-book')
                // Set text to button
                ->set('data.button.doc.text', 'L_BTN.L_DOCUMENTATION')
                // Set link to button
                ->set('data.button.doc.href', 'http://doc.phpcore.cz/');
        $data->notification = $notification->getDataToGenerate();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Settings/Language.json');

        // File model
        $file = new \App\Model\File\File();

        // Search for languages
        $file->getFiles(
            path: '/Languages/*',
            flag: \App\Model\File\File::ONLY_FOLDERS,
            function: function ( \App\Model\File\File $file, string $path ) use ($list, $system)
            {
                $JSON = new \App\Model\File\JSON('/Languages/' . basename($path) . '/Info.json');
                if ($JSON->exists())
                {
                    if ($file->exists('/Languages/' . basename($path) . '/Admin'))
                    {
                        if ($file->exists('/Languages/' . basename($path) . '/Install'))
                        {
                            if ($file->exists('/Languages/' . basename($path) . '/Website'))
                            {
                                $JSON->set('id', basename($path));

                                if (!$JSON->get('name') or !$JSON->get('version.version') or !$JSON->get('version.system'))
                                {
                                    return;
                                }
                                
                                $list->elm1('language')->appTo(data: $JSON->get(), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list ) use ($system)
                                {
                                    $list->set('data.html.ajax-id', $list->get('data.id'));
                                    
                                    // If language is default
                                    if ($system->get('site.language') === $list->get('data.id'))
                                    {
                                        $list->delete('data.button');
                                        $list->addLabel(
                                            color: 'green',
                                            text: 'L_SETTINGS.L_LANGUAGE.L_DEFAULT'
                                        );
                                    }

                                    // If language is incompatible
                                    if (!in_array(PHPCORE_VERSION, (array)$list->get('data.version.system')))
                                    {
                                        $list->addLabel(
                                            color: 'red',
                                            text: 'L_SETTINGS.L_LANGUAGE.L_INCOMPATIBLE'
                                        );
                                    }
                                });
                            }
                        }
                    }
                }
            }
        );
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
    public function deleteLanguage( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // System
        $system = $data->get('inst.system');

        // If this language is set as default
        if ($post->get('id') == $system->get('site.language'))
        {
            return false;
        }

        // File model
        $file = new \App\Model\File\File();

        // Delete language folder
        $file->delete('/Languages/' . $post->get('id') . '/*');

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
    public function activateLanguage( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Change default language
        $db->table(TABLE_SETTINGS, [
            'site.language' => $post->get('id')
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
    }
}