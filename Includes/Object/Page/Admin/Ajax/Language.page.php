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
        $get = new Get();

        $get->get('process') or exit();

        $this->data->data([
            'windowTitle' => $this->language->get('L_WINDOW_CONFIRM_ACTION'),
            'windowClose' => $this->language->get('L_NO'),
            'windowSubmit' => $this->language->get('L_YES'),
            'windowContent' => $this->language->get('L_WINDOW_DESC')[$get->get('process')],
            'status' => 'ok'
        ]);
    }
}