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
    protected string $permission = 'admin.plugin';
    
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
            'run/plugin/delete' => 'deletePlugin',
            'run/plugin/install' => 'installPlugin',
            'run/plugin/uninstall' => 'uninstallPlugin',

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
            'run/plugin/install' => [
                'id' => STRING
            ],

            'run/plugin/delete',
            'run/plugin/uninstall' => [
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
        // Navbar
        $this->navbar->elm1('settings')->elm2('plugin')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Plugin.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Plugins
        $plugins = $db->select('app.plugin.all()');

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
            ->set('data.title', 'L_NOTIFI.L_PLUGIN.L_TITLE')
            // Set title
            ->set('data.text', 'L_NOTIFI.L_PLUGIN.L_DESC')
                // set icon to button
                ->set('data.button.download.icon', 'fa-solid fa-download')
                // Set text to button
                ->set('data.button.download.text', 'L_BTN.L_AVAILABLE_PLUGINS')
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
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Plugin.json');

        // Installed plugins
        $list->elm1('installed')->fill(data: $plugins, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list )
        {
            $JSON = new \App\Model\File\JSON('/Plugins/' . $list->get('data.plugin_name_folder') . '/Info.json');
            if ($JSON->exists())
            {    
                $list
                    ->set('data.button.setup.href', '/admin/plugin/setup/' . $list->get('data.plugin_id'))
                    ->set('data.html.ajax-id', $list->get('data.plugin_id'))
                    ->set('data', array_merge($list->get('data'), $JSON->get()))
                    ->set('data.title', $list->get('data.name'))
                    ->set('data.desc', $list->get('data.desc'));

                // If plugin is incompatible
                if (!in_array(PHPCORE_VERSION, (array)$list->get('data.version.system')))
                {
                    $list->addLabel(
                        color: 'red',
                        text: 'L_PLUGIN.L_INCOMPATIBLE'
                    );
                }
            }
        });

        // File model
        $file = new \App\Model\File\File();

        // Search for plugins
        $file->getFiles(
            path: '/Plugins/*',
            flag: \App\Model\File\File::ONLY_FOLDERS,
            function: function ( \App\Model\File\File $file, string $path ) use ($plugins, $list)
            {
                if (!in_array(basename($path), array_column($plugins, 'plugin_name_folder')))
                {
                    $JSON = new \App\Model\File\JSON('/Plugins/' . basename($path) . '/Info.json');
                    if ($JSON->exists())
                    {
                        $JSON->set('id', basename($path));

                        if (!$JSON->get('name') or !$JSON->get('version.version') or !$JSON->get('version.system'))
                        {
                            return;
                        }

                        // Add plugin to list
                        $list->elm1('available')->appTo(data: $JSON->get(), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list )
                        {
                            $list
                                ->set('data.title', $list->get('data.name'))
                                ->set('data.html.ajax-id', $list->get('data.id'));

                            // If plugin is incompatible
                            if (!in_array(PHPCORE_VERSION, $list->get('data.version.system')))
                            {
                                $list->addLabel(
                                    color: 'red',
                                    text: 'L_PLUGIN.L_INCOMPATIBLE'
                                );
                            }
                        });
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
    public function deletePlugin( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Plugins
        $plugins = $db->select('app.plugin.all()');

        // If plugin is installed
        if (in_array($post->get('id'), array_column($plugins, 'plugin_name_folder')))
        {
            return false;
        }

        // File model
        $file = new \App\Model\File\File();

        // Delete plugin recursively folder
        $file->delete('/Plugins/' . $post->get('id') . '/*');

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
    public function installPlugin( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // File model
        $file = new \App\Model\File\File();

        // If SQL file exists
        if ($file->exists('/Plugins/' . $post->get('id') . '/Install.plugin.sql'))
        {
            // Execute SQL
            $db->file('/Plugins/' . $post->get('id') . '/Install.plugin.sql');
        }

        // Add plugin
        $db->insert(TABLE_PLUGINS, [
            'plugin_name_folder' => $post->get('id')
        ]);

        // Plugin
        $plugin = $data->get('inst.plugin');

        $plugin->loadInstalledPlugins();

        $plugin = $plugin->findByName($post->get('id'));

        // Load install file
        if ($file->exists('/Plugins/' . $post->get('id') . '/Install.plugin.php'))
        {
            // Install plugin
            require ROOT . '/Plugins/' . $post->get('id') . '/Install.plugin.php';
        }

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
    public function uninstallPlugin( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $plugin = $db->select('app.plugin.get()', $post->get('id'));
        if (!$plugin)
        {
            return false;
        }
        
        // File model
        $file = new \App\Model\File\File();

        // Load install file
        if ($file->exists('/Plugins/' . $post->get('id') . '/Uninstall.plugin.php'))
        {
            // Install plugin
            require ROOT . '/Plugins/' . $post->get('id') . '/Uninstall.plugin.php';
        }

        // If SQL file exists
        if ($file->exists('/Plugins/' . $post->get('id') . '/Uninstall.plugin.sql'))
        {
            // Execute SQL
            $db->file('/Plugins/' . $post->get('id') . '/Uninstall.plugin.sql');
        }

        // Delete plugin
        $db->delete( table: TABLE_PLUGINS, key: 'plugin_id', id: $post->get('id'));

        // Remove objects from sidebar
        $JSON = new \App\Model\File\JSON('/Plugins/' . $plugin['plugin_name_folder'] . '/Object/Visualization/Sidebar/Formats/Basic.json');

        if ($JSON->exists())
        {
            foreach (array_keys($JSON->get('body')) as $key)
            {
                // Move all previous sidebar objects one position down
                $db->query('
                    UPDATE ' . TABLE_SIDEBAR . '
                    LEFT JOIN ' . TABLE_SIDEBAR . '2 ON s2.position_index > s.position_index
                    SET s2.position_index = s2.position_index - 1
                    WHERE s.sidebar_object = ?
                ', [$key]);

                $db->delete(
                    table: TABLE_SIDEBAR,
                    key: 'sidebar_object',
                    id: $key
                );
            }
        }

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Refresh page
        $data->set('options.refresh', true);
    }
}