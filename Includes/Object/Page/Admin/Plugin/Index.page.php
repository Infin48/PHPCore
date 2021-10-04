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

namespace Page\Admin\Plugin;

use Block\Plugin as BlockPlugin;

use Visualization\Admin\Lists\Lists;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Index
 */
class Index extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Overall',
        'permission' => 'admin.settings'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('plugin')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        $plugin = new BlockPlugin();

        // PLUGINS
        $plugins = $plugin->getAll();

        // LIST
        $list = new Lists('/Plugin');
        $list->object('installed')->fill(data: $plugins, function: function ( \Visualization\Admin\Lists\Lists $list ) { 

            $list->obj->set->data(array_merge($list->obj->get->data(), json_decode(@file_get_contents(ROOT . '/Plugins/' . $list->obj->get->data('plugin_name_folder') . '/Info.json'), true) ?? []));
            $list->convert();

            // IF PLUGIN IN INCOMPATIBLE
            if (!in_array($this->system->get('site.version'), $list->obj->get->data('version')['system'] ?? [])) {

                $list->addLabel(
                    color: 'red',
                    text: 'L_PLUGIN_INCOMPATIBLE'
                );
            }
        });

        // SEARCH FOR PLUGINS
        foreach (glob(ROOT . '/Plugins/*', GLOB_ONLYDIR) as $path) {

            if (!in_array(basename($path), array_column($plugins, 'plugin_name_folder'))) {

                if (file_exists($path . '/Info.json')) {
                    
                    // PLUGIN DATA
                    $json = json_decode(file_get_contents($path . '/Info.json'), true);
                    $json['id'] = basename($path);

                    if (!isset($json['name']) or !isset($json['desc']) or !isset($json['version']['current']) or !isset($json['version']['system'])) {
                        continue;
                    }

                    // ADD PLUGIN TO LIST
                    $list->object('available')->appTo(data: $json, function: function ( \Visualization\Admin\Lists\Lists $list ) use ($json) {

                        // IF PLUGIN IN INCOMPATIBLE
                        if (!in_array($this->system->get('site.version'), $json['version']['system'])) {
    
                            $list->addLabel(
                                color: 'red',
                                text: 'L_PLUGIN_INCOMPATIBLE'
                            );
                        }
                    });

                }
            }
        }
        $this->data->list = $list->getData();
    }
}