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
 * Session
 */
class Session 
{
    /**
     * Checks if session exists
     *
     * @param string $session Session name
     * 
     * @return bool
     */
    public static function exists( string $session )
    {
        return isset($_SESSION[$session]);
    }
    
    /**
     * Returns value from session
     *
     * @param string $session Session name
     * 
     * @return string
     */
    public static function get( string $session )
    {
        return $_SESSION[$session] ?? '';
    }

    /**
     * Creates new session
     *
     * @param string $session Session name
     * @param mixed $value Session value
     * 
     * @return void
     */
    public static function put( string $session, mixed $value )
    {
        $_SESSION[$session] = $value;
    }

    /**
     * Deletes session
     *
     * @param string $session Session name
     * 
     * @return void
     */
    public static function delete( string $session )
    {
        unset($_SESSION[$session]);
    }
}
