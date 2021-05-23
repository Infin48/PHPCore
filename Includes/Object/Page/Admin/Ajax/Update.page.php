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

namespace Page\Admin\Ajax;

use Model\Get;

/**
 * Update
 */
class Update extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'loggedIn' => true,
        'permission' => 'admin.settings'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $get = new Get();

        if ($get->is('error')) {

            $this->data->data([
                'url' => $this->system->url->build('/admin/update/'),
                'back' => $this->language->get('L_BACK'),
                'text' => $this->language->get('L_UPDATE_ERROR'),
                'error' => $this->language->get('L_UPDATE_ERROR_DESC'),
                'status' => 'error'
            ]);

        } else {

            $API = json_decode(@file_get_contents('https://api.github.com/repos/Infin48/PHPCore/releases', false, CONTEXT), true);
            
            if (empty($API) or $API[0]['tag_name'] == $this->system->settings->get('site.version')) {
                $this->data->data([
                    'url' => $this->system->url->build('/admin/update/'),
                    'back' => $this->language->get('L_BACK'),
                    'text' => $this->language->get('L_UPDATE_INSTALLED_ALREADY'),
                    'status' => 'current'
                ]);
            } else {

                if ($this->process->call(type: 'Admin/Update', mode: 'direct', data: ['path' => $API[0]['zipball_url'], 'tag' => $API[0]['tag_name']])) {
                    $this->data->data([
                        'url' => $this->system->url->build('/admin/update/'),
                        'text' => strtr($this->language->get('L_UPDATE_INSTALLED'), ['{name}' => $API[0]['name'] ?: $API[0]['tag_name']]),
                        'back' => $this->language->get('L_BACK'),
                        'status' => 'installed',
                    ]);
                } else {

                    $this->data->data([
                        'url' => $this->system->url->build('/admin/update/'),
                        'back' => $this->language->get('L_BACK'),
                        'text' => $this->language->get('L_UPDATE_ERROR'),
                        'error' => $this->language->get('L_UPDATE_ERROR_DESC'),
                        'status' => 'error'
                    ]);
                }
            }
        }
    }
}