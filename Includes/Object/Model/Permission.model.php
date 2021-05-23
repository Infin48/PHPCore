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

/**
 * Permission
 */
class Permission 
{
    /**
     * @var array $list List of permissions
     */
    private array $list = [
        'topic' => [
            'topic.edit',
            'topic.lock',
            'topic.move',
            'topic.image',
            'topic.label',
            'topic.stick',
            'topic.create',
            'topic.delete'
        ],
        'post' => [
            'post.edit',
            'post.create',
            'post.delete'
        ],
        'profilepost' => [
            'profilepost.edit',
            'profilepost.create',
            'profilepost.delete'
        ],
        'admin' => [
            'admin.user',
            'admin.page',
            'admin.menu',
            'admin.forum',
            'admin.index',
            'admin.group',
            'admin.label',
            'admin.template',
            'admin.settings',
            'admin.notification'
        ]
    ];

    /**
     * @var array $userPermission Stored logged user permission
     */
    private array $userPermission = [];

    /**
     * @var int $data Logged user group index
     */
    private int $index = 0;

    /**
     * @var bool $data If true - logged user is admin otherwise false
     */
    private bool $admin = false;
    
    /**
     * Sets index to logged user
     *
     * @return void
     */
    public function setIndex( int $index )
    {
        $this->index = $index;
    }

    /**
     * Sets logged user as admin
     *
     * @return void
     */
    public function admin()
    {
        $this->admin = true;
    }
    
    /**
     * Returns list of permissions by groups
     *
     * @return array
     */
    public function getList()
    {
        $list = $this->list;
        array_push($list['admin'], '*');

        return $list;
    }

    /**
     * Returns all permissions without division by groups
     *
     * @return array
     */
    public function getPermissions()
    {
        $_permissions = [];
        foreach ($this->list as $permissions) {
            $_permissions = array_merge($_permissions, $permissions);
        }

        array_push($_permissions, '*');

        return $_permissions;
    }
    
    /**
     * Checks if logged user has given permission
     *
     * @param  array|string $permission
     * 
     * @return bool If true - logged user has given permission otherwise false
     */
    public function has( array|string $permission )
    {
        if (is_array($permission)) {
            foreach ($permission as $item) {
                if ($this->has($item) === true) {
                    return true;
                }
            }

            return false;
        }

        if (($ex = explode('.', $permission))[1] == '?') {

            foreach ($this->userPermission as $permission) {

                if (explode('.', $permission)[0] == $ex[0]) {
                    return true;
                }
            }
            return false;
        }


        if (in_array($permission, $this->userPermission)) {
            return true;
        }

        return false;
    }
    
    /**
     * Disables given permission to current page
     *
     * @param  string $permission
     * 
     * @return void
     */
    public function disable( string $permission )
    {
        if (($ex = explode('.', $permission))[1] == '*') {
            foreach ($this->userPermission as $key => $value) {
                if (explode('.', $value)[0] == $ex[0]) {
                    unset($this->userPermission[$key]);
                }

            }
        } else {
            if (in_array($permission, $this->userPermission)) {
                unset($this->userPermission[array_search($permission, $this->userPermission)]);
            }
        }
    }
    
    /**
     * Sets permissions to logged user
     *
     * @param  array $permissions
     * 
     * @return void
     */
    public function set( array $permissions )
    {
        if (in_array('*', $permissions) or $this->admin === true) {

            foreach ($this->list as $_permissions) {
                $this->userPermission = array_merge($this->userPermission, $_permissions);
            }
            return;

        }
        $this->userPermission = $permissions;
    }

    /**
     * Compare given index with index of logged user
     *
     * @param  int $index
     * 
     * @return bool
     */
    public function index( int $index )
    {
        if ($this->admin === true) {
            return true;
        }

        if ($this->index > $index) {
            return true;
        }
        
        return false;
    }

    /**
     * Compare given user with logged user
     *
     * @param  int $index User index
     * @param  bool $admin If true - user is admin otherwise false
     * 
     * @return bool
     */
    public function compare( int $index, bool $admin = false )
    {
        if ($this->admin === true) {
            return true;
        }

        if ($admin !== true) {
            if ($index < $this->index) {
                return true;
            }
        }
        
        return false;
    }
}
