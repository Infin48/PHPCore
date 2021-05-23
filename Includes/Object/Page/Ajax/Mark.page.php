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

namespace Page\Ajax;

/**
 * Mark
 */
class Mark extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'loggedIn' => true
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        if ($this->process->call(type: 'User/Mark', mode: 'direct')) {
            $this->data->data([
                'empty' => $this->language->get('L_NAVBAR')['L_NOTIFICATION_NO'],
                'status' => 'ok'
            ]);
        }
    }
}