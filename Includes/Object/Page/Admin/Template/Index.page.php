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

namespace App\Page\Admin\Template;

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
    protected string $permission = 'admin.template';

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
            'run/template/delete' => 'deleteTemplate',
            'run/template/preview' => 'previewTemplate',
            'run/template/activate' => 'activateTemplate',

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
            'run/template/delete',
            'run/template/preview',
            'run/template/activate' => [
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

        // Navbar
        $this->navbar->elm1('appearance')->elm2('template')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Template.json');
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
            ->set('data.title', 'L_NOTIFI.L_TEMPLATE.L_TITLE')
            // Set title
            ->set('data.text', 'L_NOTIFI.L_TEMPLATE.L_DESC')
                // set icon to button
                ->set('data.button.download.icon', 'fa-solid fa-download')
                // Set text to button
                ->set('data.button.download.text', 'L_BTN.L_AVAILABLE_TEMPLATES')
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
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Template.json');

        // File model
        $file = new \App\Model\File\File();

        // Search for templates
        $file->getFiles(
            path: '/Styles/*',
            flag: \App\Model\File\File::ONLY_FOLDERS|\App\Model\File\File::DO_NOT_NEST,
            function: function ( \App\Model\File\File $file, string $path ) use ($list, $system, $language)
            {
                $JSON = new \App\Model\File\JSON('/Styles/' . basename($path) . '/Info.json');
                if ($JSON->exists())
                {
                    // Language data
                    $JSON->set('id', basename($path));
                    $JSON->set('template_name_folder', basename($path));
                    
                    if (!$JSON->get('name') or !$JSON->get('version.version') or !$JSON->get('version.system'))
                    {
                        return;
                    }

                    $list->elm1('templates')->appTo(data: $JSON->get(), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list ) use ($file, $system, $language)
                    {
                        $list
                            ->set('data.title', $list->get('data.name'))
                            ->set('data.html.ajax-id', $list->get('data.id'))
                            ->set('data.button.setup.href', '/admin/template/setup/name-' . $list->get('data.id'));;

                        if ($file->exists('/Styles/' . $list->get('data.id') . '/Object/Page/Index.page.php') or $list->get('data.info'))
                        {
                            $list->show('data.button.setup');
                        }

                        // If template is default
                        if ($list->get('data.id') === 'Default') {
                            $list->delete('data.button.delete');
                        }

                        // If template has header image
                        if ($list->get('data.image')) {

                            if (file_exists(ROOT . '/Styles/' . $list->get('data.id') . $list->get('data.image'))) {

                                $list->set('data.image', '/Styles/' . $list->get('data.id') . $list->get('data.image'));
                                
                            } else $list->set('data.image', '');
                        } else $list->set('data.image', '');

                        // If template is set as default
                        if ($system->get('site.template') === $list->get('data.id')) {

                            $list->delete('data.button.delete')
                                ->delete('data.button.preview')
                                ->delete('data.button.activate');
                                
                            $list->addLabel(
                                color: 'green',
                                text: 'L_TEMPLATE.L_DEFAULT'
                            );
                        }

                        // If author is not set
                        if (!$list->get('data.author.name'))
                        {
                            // Set author name as unknown
                            $list->set('data.author.name', $language->get('L_UNKNOWN'));
                        }

                        // If template is incompatible
                        if (!in_array(PHPCORE_VERSION, (array)$list->get('data.version.system'))) {

                            $list->addLabel(
                                color: 'red',
                                text: 'L_TEMPLATE.L_INCOMPATIBLE'
                            );
                        }
                    });

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
    public function deleteTemplate( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // If template is not default
        if ($post->get('id') === 'Default')
        {
            return false;
        }

        // File model
        $file = new \App\Model\File\File();

        // Delete template folder
        $file->delete('/Styles/' . $post->get('id') . '/*');

        $db->query('
            DELETE FROM `phpcore_settings` WHERE `key` LIKE "template.' . strtolower($post->get('id')) . '.%" 
        ');

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
    public function activateTemplate( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Set template as default
        $db->table(TABLE_SETTINGS, [
            'site.template' => $post->get('id')
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
    public function previewTemplate( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Put template to session
        \App\Model\Session::put('preview', $post->get('id'));

        // Redirect to index page
        $data->set('data.redirect', INDEX);
    }
}