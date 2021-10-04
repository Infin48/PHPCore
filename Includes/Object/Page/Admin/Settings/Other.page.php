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

use Model\File\File;

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
        $this->navbar->object('settings')->row('settings')->active()->option('others')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FILE
        $file = new File();

        // LOADS EDITOR LANGUAGES FROM FOLDER
        $languageEditor = $file->getFiles('/Assets/Trumbowyg/langs/*.min.js', File::PATH_REMOVE|File::EXTENSION_REMOVE_FULL|File::FOLDER_SKIP);
        $list = [['title' => 'en', 'value' => 'en']];
        foreach ($languageEditor as $lang) {
            $list[] = [
                'title' => $lang,
                'value' => $lang
            ];
        }

        // FIELD
        $field = new Field('/Admin/Settings/Other');
        $field->data($this->system->get());
        $field->object('language')->row('site.language_editor')->fill(data: $list);
        $this->data->field = $field->getData();

        // EDIT SETTINGS
        $this->process->form(type: '/Admin/Settings/Other', data: [
            'options' => [
                'input' => [
                    'site_language_editor' => array_column($list, 'value')
                ]
            ]
        ]);
    }
}