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
 * Language
 */
class Language extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'loggedIn' => true,
        'permission' => 'admin.?'
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

            require: ['process'],

            exec: function ( \Model\Ajax $ajax ) {

                if (!isset($this->language->get('L_WINDOW')['L_DESC'][$ajax->get('process')])) {
                    $ajax->end();
                }

                $ajax->data([
                    'windowTitle' => $this->language->get('L_WINDOW')['L_TITLE']['L_CONFIRM'],
                    'windowClose' => $this->language->get('L_NO'),
                    'windowSubmit' => $this->language->get('L_YES'),
                    'windowContent' => $this->language->get('L_WINDOW')['L_DESC'][$ajax->get('process')]
                ]);
                $ajax->ok();
            }
        );
        $ajax->end();
    }
}