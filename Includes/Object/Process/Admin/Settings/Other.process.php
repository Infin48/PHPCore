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

use Model\File;

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
            'site_language'                  => [
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

        $file = new File();
        $settings = $this->system->settings->get();

        $file->load('site_background_image');
        if ($file->check(false)) {
            $file->upload('Site/Header');
            $settings['site.background_image'] = $file->getFormat();
        }

        $file->load('site_favicon');
        if ($file->check(false)) {
            $file->upload('Site/Favicon');
            $settings['site.favicon'] = $file->getFormat();
        }

        if ($this->data->is('delete_site_background_image')) {
            $settings['site.background_image'] = '';
            $file->deleteImage('Site/Header');
        }

        if ($this->data->is('delete_site_favicon')) {
            $settings['site.favicon'] = '';
            $file->deleteImage('Site/Favicon');
        }

        $settings['site.locale'] = $this->data->get('site_locale');
        $settings['site.timezone'] = $this->data->get('site_timezone');
        $settings['site.language'] = $this->data->get('site_language');
        $settings['site.language_editor'] = $this->data->get('site_language_editor');
        $settings['site.background_image_position'] = $this->data->get('site_background_image_position');

        $this->system->settings->set($settings);

        $this->updateSession();
        file_put_contents(ROOT . '/Assets/Trumbowyg/trumbowyg.min.js', strtr(file_get_contents(ROOT . '/Assets/Trumbowyg/trumbowygOrg.min.js'), ['{language}' => $this->data->get('site_language_editor')]));
    
        // ADD RECORD TO LOG
        $this->log();
    }
}