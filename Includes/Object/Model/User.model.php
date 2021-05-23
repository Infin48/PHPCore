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

namespace Model;

use Block\User as BlockUser;

use Model\Cookie;
use Model\Permission;
use Model\Database\Query;

/**
 * User
 */
class User
{
    /**
     * @var \Model\Permission $perm Permission
     */
    public \Model\Permission $perm;

    /**
     * @var bool $admin If true - logged user is admin otherwise false
     */
    public bool $admin = false;

    /**
     * @var int $index Logged user group index
     */
    public int $index = 0;

    /**
     * @var array $data Logged user data
     */
    private array $data = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new Query();
        $this->perm = new Permission();

        $hash = Cookie::exists('token') ? Cookie::get('token') : Session::get('token');

        if ($hash) {

            $user = new BlockUser();

            if ($this->data = $user->getByHash((string)$hash)) {

                $this->data['unread'] = $user->getUnread($this->data['user_id']);
                $this->data['user_last_activity'] = date(DATE);
                $this->data['groupPermission'] = array_filter(explode(',', $this->data['group_permission']));
                $this->data['group_index'] = $this->data['is_admin'] == 1 ? 9999999999 + 1 : (int)$this->data['group_index'];

                $this->index = $this->data['group_index'];
                $this->admin = (bool)$this->data['is_admin'];

                if ($this->data['is_admin'] == 1) {
                    $this->perm->admin();
                }
                $this->perm->setIndex($this->data['group_index']);
                $this->perm->set($this->data['groupPermission']);

                // UPDATE LAST ACTIVITY
                $this->db->update(TABLE_USERS, [
                    'user_last_activity' => DATE_DATABASE
                ], $this->data['user_id']);

                return true;


            }
        }

        return false;
    }

    /**
     * Checks if user is logged
     *
     * @return bool
     */
    public function isLogged() 
    {
        if (empty($this->data) === false) {
            return true;
        }
        
        return false;
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
        if (is_null($value)) {
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
