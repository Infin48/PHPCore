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
     * @var string $name Language name
     */
    private string $name = '';

    /**
     * @var array $language Language
     */
    private static array $language = [];

    /**
     * Constructor
     * 
     * @param string $language Language name
     */
    public function __construct( string $language = null, bool $admin = false, array $plugins = [] )
    {
        if ($language) {

            $this->name = $language;

            $path = '/Languages/' . $language;
            if ($admin) {
                $path .= '/Admin';
            }

            require ROOT . $path . '/Load.language.php';
            self::$language = $language;

            foreach ($plugins as $item) {

                $url = ROOT . '/Plugins/' . $item . '/Languages/' . $this->name;

                if ($admin === true) {
                    $url .= '/Admin';
                }

                if (file_exists($url . '/Load.language.php')) {
                    
                    require $url . '/Load.language.php';

                    self::$language = array_merge_recursive(self::$language, $language);
                }
            } 
        }
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