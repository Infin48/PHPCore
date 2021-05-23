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

namespace Process\User;

use Model\Cookie;
use Model\Session;

/**
 * Logout
 */
class Logout extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [];

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
        $this->db->query('UPDATE ' . TABLE_USERS . ' SET user_hash = ?, user_last_activity = NOW() WHERE user_id = ?', [md5(uniqid(mt_rand(), true)), LOGGED_USER_ID]);

        // DELETE COOKIES
        Cookie::delete('token');

        // DELETE SESSIONS
        Session::delete('token');
    }
}