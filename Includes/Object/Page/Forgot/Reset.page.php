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

namespace Page\Forgot;

use Block\User;

use Visualization\Field\Field;

/**
 * Reset
 */
class Reset extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => string,
        'template' => '/Forgot/Change',
        'loggedOut' => true
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // BLOCK
        $user = new user();

        $data = $user->getByForgotCode((string)$this->url->getID());

        if (!$data) {
            redirect('/');
        }

        // FIELD
        $field = new Field('/User/Forgot/Change');
        $this->data->field = $field->getData();

        // RESET PROCESS
        $this->process->form(type: '/Forgot/Reset', data: [
            'user_id'   => $data['user_id'],
            'options'   => [
                'url'   => '/'
            ]
        ]);
    }
}