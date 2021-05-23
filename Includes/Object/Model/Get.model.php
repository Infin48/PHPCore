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
 * Get
 */
class Get
{    
    /**
     * Returns parameter from url
     *
     * @param  string $key
     * 
     * @return string
     */
    public function get( string $key )
    {
        return preg_replace("/[^A-Za-z0-9_\/]/", '', $_GET[$key] ?? '');
    }
    
    /**
     * Checks if given parameter is in url
     *
     * @param  string $key
     * 
     * @return bool
     */
    public function is( string $key )
    {
        return isset($_GET[$key]);
    }
}