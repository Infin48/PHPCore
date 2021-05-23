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

namespace Page\Admin\Settings;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Other
 */
class Other extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'Overall',
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
        $this->navbar->object('settings')->row('settings')->active()->option('others')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // LOADS EDITOR LANGUAGES FROM FOLDER
        $languageEditor = array_map(function($dir) {
            return [
                'title' => basename($dir, '.min.js'),
                'value' => basename($dir, '.min.js')
            ];
        }, glob(ROOT . '/Assets/Trumbowyg/langs/*.min.js'));

        $languageEditor[] = ['title' => 'en', 'value' => 'en'];

        // LOADS WEBSITE LANGUAGES
        foreach (glob(ROOT . '/Languages/*', GLOB_ONLYDIR) as $dir) {
            if (!file_exists($dir . '/Info.json')) continue;

            $short = array_pop(array_filter(explode('/', $dir)));
            $json = json_decode(file_get_contents($dir . '/Info.json'), true);
            $language[] = [
                'title' => $json['name'] ?? '',
                'value' => $short,
            ];
        }

        // FIELD
        $field = new Field('Admin/Settings/Other');
        $field->data($this->system->settings->get());
        $field->object('language')
            ->row('site.language')->fill($language)
            ->row('site.language_editor')->fill($languageEditor);
        $this->data->field = $field->getData();

        // EDIT SETTINGS
        $this->process->form(type: 'Admin/Settings/Other', data: [
            'options' => [
                'input' => [
                    'site_language' => array_column($language, 'value'),
                    'site_language_editor' => array_column($languageEditor, 'value')
                ]
            ]
        ]);
    }
}