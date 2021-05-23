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

/**
 * Mark
 */
class Mark extends \Process\ProcessExtend
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
        // MARK ALL USER NOTIFICATIONS AD READ
        $this->db->query('
            DELETE un FROM ' . TABLE_USERS_NOTIFICATIONS . '
            WHERE to_user_id = ?
        ', [LOGGED_USER_ID]);
    }
}