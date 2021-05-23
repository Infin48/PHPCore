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
 * Cookie
 */
class Cookie 
{
    /**
     * Checks if cookie exists
     *
     * @param string $cookie Cookie name
     * 
     * @return bool
     */
    public static function exists( string $cookie )
    {
        return isset($_COOKIE[$cookie]);
    }

    /**
     * Returns value from cookie
     *
     * @param string $cookie
     * 
     * @return mixed
     */
    public static function get( string $cookie )
    {
        return $_COOKIE[$cookie] ?? '';
    }

    /**
     * Creates new cookie
     *
     * @param string $cookie Cookie name
     * @param mixed $value Cookie value
     * @param int $expiry Expiry
     * 
     * @return void
     */
    public static function put( string $cookie, mixed $value, int $expiry = 0 )
    {
        setcookie($cookie, $value, time() + $expiry, '/', null, null, true);
    }

    /**
     * Deletes cookie
     *
     * @param string $cookie Cookie name
     * 
     * @return void
     */
    public static function delete( string $cookie )
    {
        self::put($cookie, '', -3600);
    }
}
