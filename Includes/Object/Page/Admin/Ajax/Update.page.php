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

use Model\Ajax;

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
        $ajax = new Ajax();

        $ajax->ajax(

            exec: function ( \Model\Ajax $ajax ) {

                if ($ajax->is('error')) {
                    
                    $ajax->data([
                        'url' => $this->url->build('/admin/update/'),
                        'back' => $this->language->get('L_BACK'),
                        'text' => $this->language->get('L_UPDATE_ERROR'),
                        'error' => $this->language->get('L_UPDATE_ERROR_DESC')
                    ]);
                    $ajax->error();
                    $ajax->end();
                }

                $API = json_decode(@file_get_contents('https://api.github.com/repos/Infin48/PHPCore/releases', false, CONTEXT), true);

                if (empty($API) or $API[0]['tag_name'] == $this->system->get('site.version')) {
                    $ajax->data([
                        'url' => $this->url->build('/admin/update/'),
                        'back' => $this->language->get('L_BACK'),
                        'text' => $this->language->get('L_UPDATE_INSTALLED_ALREADY'),
                        'status' => 'current'
                    ]);
                } else {

                    $ajax->process(

                        process: $this->process,

                        type: 'Admin/Update',
                        data: [
                            'tag' => $API[0]['tag_name'],
                            'path' => $API[0]['zipball_url']
                        ],

                        success: function ( \Model\Ajax $ajax ) use ($API) {

                            $ajax->data([
                                'url' => $this->url->build('/admin/update/'),
                                'text' => strtr($this->language->get('L_UPDATE_INSTALLED'), ['{name}' => $API[0]['name'] ?: $API[0]['tag_name']]),
                                'back' => $this->language->get('L_BACK')
                            ]);
                            $ajax->status('installed');

                        },

                        failure: function ( \Model\Ajax $ajax ) {

                            $ajax->data([
                                'url' => $this->url->build('/admin/update/'),
                                'back' => $this->language->get('L_BACK'),
                                'text' => $this->language->get('L_UPDATE_ERROR'),
                                'error' => $this->language->get('L_UPDATE_ERROR_DESC')
                            ]);
                            $ajax->error();
                        }
                    );
                }
            }
        );
        $ajax->end();
    }
}