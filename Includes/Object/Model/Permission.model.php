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

/**
 * Permission
 */
class Permission 
{
    /**
     * @var array $list List of permissions
     */
    private static array $list = [
        'article' => [
            'article.create',
            'article.edit',
            'article.label',
            'article.delete',
            'article.stick'
        ],
        'post' => [
            'post.edit',
            'post.create',
            'post.delete'
        ],
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
        'profilepost' => [
            'profilepost.edit',
            'profilepost.create',
            'profilepost.delete'
        ],
        'image' => [
            'image.gif'
        ],
        'admin' => [
            'admin.settings',
            'admin.url',
            'admin.plugin',
            'admin.notification',
            'admin.page',
            'admin.menu',
            'admin.group',
            'admin.user',
            'admin.role',
            'admin.template',
            'admin.sidebar',
            'admin.forum',
            'admin.label',
            'admin.log',
            'admin.index'
        ]
    ];

    /**
     * @var array $userPermission Stored logged user permission
     */
    private static array $userPermission = [];
    
    /**
     * Constructor
     *
     * @param  array $permission List of permission which will be loaded
     * @param  int $id Group ID of logged user
     */
    public function __construct( array $permission = [], int $id = null )
    {
        if (is_null($id))
        {
            return;
        }
        
        if (in_array('*', $permission) or $id == 1)
        {
            foreach (self::$list as $_permissions)
            {
                self::$userPermission = array_merge(self::$userPermission, $_permissions);
            }

            return;
        }
        self::$userPermission = $permission;
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
        $list = self::$list;
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
        foreach (self::$list as $permissions) {
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
    public static function has( array|string $permission )
    {
        if (is_array($permission))
        {
            foreach ($permission as $item)
            {
                if (self::has($item) === true)
                {
                    return true;
                }
            }

            return false;
        }

        if (($ex = explode('.', $permission))[1] == '?')
        {
            foreach (self::$userPermission as $permission)
            {
                if (explode('.', $permission)[0] == $ex[0])
                {
                    return true;
                }
            }
            return false;
        }

        if (in_array($permission, self::$userPermission))
        {
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
        if (($ex = explode('.', $permission))[1] == '*')
        {
            foreach (self::$userPermission as $key => $value)
            {
                if (explode('.', $value)[0] == $ex[0])
                {
                    unset(self::$userPermission[$key]);
                }

            }
        } else {
            if (in_array($permission, self::$userPermission))
            {
                unset(self::$userPermission[array_search($permission, self::$userPermission)]);
            }
        }
    }

    /**
     * Adds category
     *
     * @param  string $category Permisison category 
     * 
     * @return void
     */
    public static function addCategory( string $category )
    {
        self::$list[$category] = [];
    }

    /**
     * Adds permission
     *
     * @param  string $category Permisison category 
     * @param  string $permission Permission
     * 
     * @return void
     */
    public static function addPermission( string $category, string $permission )
    {
        array_push(self::$list[$category], $category . '.' . $permission);
    }
}
