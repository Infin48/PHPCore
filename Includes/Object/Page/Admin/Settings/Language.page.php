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

use Visualization\Admin\Lists\Lists;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Language
 */
class Language extends \Page\Page
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
        $this->navbar->object('settings')->row('settings')->active()->option('language')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // LIST
        $list = new Lists('/Settings/Language');

        // SEARCH FOR LANGUAGES
        foreach (glob(ROOT . '/Languages/*', GLOB_ONLYDIR) as $dir) {

            if (file_exists($dir . '/Info.json') and file_exists($dir . '/Load.language.php')) {

                // LANGUAGE DATA
                $json = json_decode(file_get_contents($dir . '/Info.json'), true);
                $json['id'] = basename($dir);

                if (!isset($json['name']) or !isset($json['version']['current']) or !isset($json['version']['system'])) {
                    continue;
                }

                $list->object('language')->appTo(data: $json, function: function ( \Visualization\Admin\Lists\Lists $list ) use ($dir) { 

                    // IF LANGUAGE IS DEFAULT
                    if ($this->system->get('site.language') === basename($dir)) {

                        $list->delButton([
                            'activate', 'delete'
                        ]);
                        $list->addLabel(
                            color: 'green',
                            text: 'L_SETTINGS_LANGUAGE_DEFAULT'
                        );
                    }

                    // IF LANGUAGE IS INCOMPATIBLE
                    if (!in_array($this->system->get('site.version'), $list->obj->get->data('version')['system'])) {
                        
                        $list->addLabel(
                            color: 'red',
                            text: 'L_SETTINGS_LANGUAGE_INCOMPATIBLE'
                        );
                    }
                });
            }
        }

        $this->data->list = $list->getData();
    }
}