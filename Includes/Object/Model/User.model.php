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

namespace App\Model;

use \App\Model\Cookie;

/**
 * User
 */
class User
{
    /**
     * @var \App\Model\Permission $perm Permission
     */
    public \App\Model\Permission $perm;

    /**
     * @var bool $admin If true - logged user is admin otherwise false
     */
    public bool $admin = false;

    /**
     * @var bool $logged If true - user is logged
     */
    public bool $logged = false;

    /**
     * @var int $index Logged user group index
     */
    public int $index = 0;

    /**
     * @var array $data Logged user data
     */
    private array $data = [
        'user_id'       => 0,
        'group_id'      => 0,
        'user_name'     => 'visitor',
        'group_class'   => 'visitor',
        'group_index'   => 0
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {        
        $db = new \App\Model\Database\Query();
        $this->perm = new \App\Model\Permission();

        $data = $this->data;
        $data['permission'] = new \App\Model\Permission();
        $hash = Cookie::exists('token') ? Cookie::get('token') : Session::get('token');
        if ($hash)
        {
            if ($_ = $db->select('app.user.byHash()', $hash))
            {
                $data = $_;
                
                $data['unread'] = $db->select('app.user.unread()', $data['user_id']);
                $data['user_last_activity'] = date(DATE);
                $data['permission'] = new \App\Model\Permission(
                    permission: array_filter(explode(',', $data['group_permission'])),
                    id: $data['group_id']
                );

                $this->logged = true;

                // Update last activity
                $db->update(TABLE_USERS, [
                    'user_last_activity' => DATE_DATABASE
                ], $data['user_id']);
            }
        }
        $this->data = $data;
        if (!defined('LOGGED_USER_ID'))
        {
            // User constants
            define('LOGGED_USER_ID', $this->get('user_id'));
            define('LOGGED_USER_NAME', $this->get('user_name'));
            define('LOGGED_USER_GROUP_CLASS', $this->get('group_class'));
            define('LOGGED_USER_GROUP_INDEX', $this->get('group_index'));
            define('LOGGED_USER_GROUP_ID', $this->get('group_id'));
        }
    }

    /**
     * Checks if user is logged
     *
     * @return bool
     */
    public function isLogged() 
    {
        return $this->logged;
    }

    /**
     * Returns value from user data
     *
     * @param string|null $value If null - method returns all user data
     * 
     * @return string|array
     */
    public function get( string $value = null )
    {
        if (is_null($value))
        {
            return $this->data;
        }
        return $this->data[$value] ?? '';
    }

    /**
     * Sets value to logged user data
     *
     * @param string $key 
     * @param mixed $value
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $this->data[$key] = $value;
    }
}
