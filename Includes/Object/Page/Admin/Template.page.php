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

namespace Page\Admin;

use Visualization\Admin\Lists\Lists;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Template
 */
class Template extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Overall',
        'permission' => 'admin.template'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('template')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();
        
        // LIST
        $list = new Lists('/Template');

        // SEARCH FOR TEMPLATES
        foreach (glob(ROOT . '/Styles/*', GLOB_ONLYDIR) as $path) {

            if (file_exists($path . '/Info.json')) {

                // TEMPLATE DATA
                $json = json_decode(file_get_contents($path . '/Info.json'), true);
                $json['id'] = basename($path);
                $json['template_name_folder'] = basename($path);

                if (!isset($json['name']) or !isset($json['desc']) or !isset($json['version']['current']) or !isset($json['version']['system'])) {
                    continue;
                }

                $list->object('templates')->appTo(data: $json, function: function ( \Visualization\Admin\Lists\Lists $list ) use ($path) { 

                    // IF TEMPLATE IS DEFAULT
                    if (basename($path) === 'Default') {
                        $list->delButton('delete');
                    }

                    // IF TEMPLATE HAS HEADER IMAGE
                    if ($list->obj->get->data('image')) {

                        if (file_exists(ROOT . '/Styles/' . basename($path) . $list->obj->get->data('image'))) {

                            $list->obj->set->data('image', '/Styles/' . basename($path) . $list->obj->get->data('image'));
                            
                        } else $list->obj->set->data('image', '');
                    } else $list->obj->set->data('image', '');

                    // IF TEMPLATE IS SET AS DEFAULT
                    if ($this->system->get('site.template') === basename($path)) {

                        $list->delButton([
                            'delete', 'activate', 'preview'
                        ]);
                        $list->addLabel(
                            color: 'green',
                            text: 'L_TEMPLATE_DEFAULT'
                        );
                    }

                    // IF TEMPLATE IS INCOMPATIBLE
                    if (!in_array($this->system->get('site.version'), $list->obj->get->data('version')['system'])) {

                        $list->addLabel(
                            color: 'red',
                            text: 'L_TEMPLATE_INCOMPATIBLE'
                        );
                    }
                });
            }
        }

        $this->data->list = $list->getData();
    }
}