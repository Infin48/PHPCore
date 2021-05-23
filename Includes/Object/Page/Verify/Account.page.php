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

namespace Page\Verify;

/**
 * Account
 */
class Account extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => string,
        'loggedOut' => true
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // VERIFY USER
        $this->process->call(type: 'User/Verify/Account', mode: 'silent', url: '/', data: [
            'account_code' => $this->getID()
        ]);
    }
}