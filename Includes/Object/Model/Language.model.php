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
 * Language
 */
class Language
{
    /**
     * @var array $language Language
     */
    private static array $language = [];

    /**
     * Loads language
     *
     * @param  string $path Path to language
     * 
     * @return void
     */
    public function load( string $path )
    {
        require ROOT . $path . '/Load.language.php';
        static::$language = $language;
    }

    /**
     * Returns given key from language
     *
     * @param  string|null $string If null - returns whole language
     * 
     * @return mixed
     */
    public function get( string $key = null )
    {
        if (is_null($key)) {
            return static::$language;
        }

        return static::$language[$key] ?? '';
    }
}