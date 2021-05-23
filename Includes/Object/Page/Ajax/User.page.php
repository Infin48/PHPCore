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

use Block\User as BlockUser;

use Model\Get;

/**
 * User
 */
class User extends \Page\Page
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
        $get = new Get();

        $get->get('user') or exit();

        $user = new BlockUser();

        if ($data = $user->getByName($get->get('user'))) {
            if ($data['user_id'] != LOGGED_USER_ID) {
                        
                $this->data->data([
                    'user' => $this->build->user->linkImg($data),
                    'status' => 'ok',
                    'user_id' => $data['user_id']
                ]);
            }
        }
    }
}