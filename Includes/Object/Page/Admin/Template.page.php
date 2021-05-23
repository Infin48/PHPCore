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

use Visualization\Lists\Lists;
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
        'template' => 'Overall',
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
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        $templates = [];

        foreach (glob(ROOT . '/Styles/*', GLOB_ONLYDIR) as $path) {

            if (file_exists($path . '/Info.json')) {

                $json = json_decode(file_get_contents($path . '/Info.json'), true);

                if (basename($path) != $this->system->settings->get('site.template')) {
                    $templates[] = [
                        'template_name' => $json['name'],
                        'template_name_folder' => basename($path)
                    ];
                }
            }
        }
        
        // LIST
        $list = new Lists('Admin/Template');
        $list->object('current')->appTo(['template_name' => $this->system->template->get('name')]);

        foreach ($templates as $item) {
            $list->object('loaded')->appTo($item)->jumpTo();

            if ($item['template_name_folder'] === 'Default') {
                $list->delButton('delete');
            }
        }

        $this->data->list = $list->getData();

        // REFRESH TEMPLATE
        $this->process->call(type: 'Admin/Template/Refresh', on: $this->url->is('refresh'), data: [
            'template_name' => $this->system->template->get('name')
        ]);

        // TEMPLATES FOLDER NAMES
        $templatesNamesFolder = array_column($templates, 'template_name_folder');

        // SET TEMPLATE AS DEFAULT
        if (in_array($this->url->get('set'), $templatesNamesFolder)) {

            $this->process->call(type: 'Admin/Template/Set', on: $this->url->is('set'), data: [
                'template_name' => $templates[array_search($this->url->get('set'), $templatesNamesFolder)]['template_name'],
                'template_name_folder' => $this->url->get('set')
            ]);
        }
    }
}