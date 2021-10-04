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

namespace Process\Admin\Settings;

use Model\File\File;
use Model\File\Text;

/**
 * Other
 */
class Other extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'delete_site_background_image'   => [
                'type' => 'checkbox'
            ],
            'delete_site_favicon'            => [
                'type' => 'checkbox'
            ],
            'site_background_image_position' => [
                'custom' => ['top', 'center', 'bottom']
            ],
            'site_locale'                    => [
                'type' => 'text',
                'required' => true
            ],
            'site_timezone'                  => [
                'type' => 'text',
                'required' => true
            ],
            'site_language_editor'           => [
                'type' => 'text',
                'required' => true
            ]
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if (!in_array($this->data->get('site_timezone'), \DateTimeZone::listIdentifiers())) {
            return false;
        }

        if (!in_array($this->data->get('site_locale'), \ResourceBundle::getLocales(''))) {
            return false;
        }

        $settings = [
            'site.locale' => $this->data->get('site_locale'),
            'site.timezone' => $this->data->get('site_timezone'),
            'site.language_editor' => $this->data->get('site_language_editor'),
            'site.background_image_position' => $this->data->get('site_background_image_position')
        ];

        $file = new File();

        $image = $file->form('site_background_image', FILE_TYPE_IMAGE);
        $image->ignoreLimit();

        if ($image->check()) {
            $image->upload('/Uploads/Site/Header');
            $settings['site.background_image'] = $image->getFormat();
        }

        $image = $file->form('site_favicon', FILE_TYPE_IMAGE);
        $image->ignoreLimit();
        
        if ($image->check()) {
            $image->upload('/Uploads/Site/Favicon');
            $settings['site.favicon'] = $image->getFormat();
        }

        if ($this->data->is('delete_site_background_image')) {
            $settings['site.background_image'] = '';
            $file->deleteImage('/Site/Header');
        }

        if ($this->data->is('delete_site_favicon')) {
            $settings['site.favicon'] = '';
            $file->deleteImage('/Site/Favicon');
        }

        $this->db->table(TABLE_SETTINGS, $settings);

        $text = new Text('/Assets/Trumbowyg/trumbowygOrg.min.js');
        $text->set('language', $this->data->get('site_language_editor'));
        $text->save('/Assets/Trumbowyg/trumbowyg.min.js');

        // UPDATE SESSIONS
        $this->db->table(TABLE_SETTINGS, [
            'session' => RAND,
            'session.scripts' => RAND
        ]);

        // ADD RECORD TO LOG
        $this->log();
    }
}