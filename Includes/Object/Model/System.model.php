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

use Block\Settings;

/**
 * System
 */
class System
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

            $settings = new Settings();
            self::$settings = $settings->getAll();
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
}
