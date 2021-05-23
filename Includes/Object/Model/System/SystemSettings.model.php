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

namespace Model\System;

/**
 * SystemSettings
 */
class SystemSettings
{
    /**
     * @var array $settings List of system settings
     */
    public static array $settings = [];
        
    /**
     * Constructor
     */
    public function __construct()
    {
        if (!self::$settings) {
            self::$settings = json_decode(file_get_contents(ROOT . '/Includes/Settings/Settings.json'), true);
        }
    }
    
    /**
     * Returns value from system settings
     *
     * @param  string|null $key If null - returns whole system settings
     * 
     * @return mixed
     */
    public function get( string $key = null )
    {
        if (is_null($key)) {
            return self::$settings;
        }

        return self::$settings[$key] ?? '';
    }
    
    /**
     * Sets value to system settings
     *
     * @param  string|array $key If is array - replaces whole system settings with this array
     * @param  mixed $value
     * 
     * @return void
     */
    public function set( string|array $key, mixed $value = null )
    {
        if (is_array($key)) {
            file_put_contents(ROOT . '/Includes/Settings/Settings.json', json_encode(array_merge(self::$settings, $key), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return;
        }

        self::$settings[$key] = $value;

        file_put_contents(ROOT . '/Includes/Settings/Settings.json', json_encode(self::$settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
