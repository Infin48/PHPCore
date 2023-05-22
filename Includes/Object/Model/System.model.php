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
 * System
 */
class System
{
    /**
     * @var array $data List of system settings
     */
    private array $data = [];
        
    /**
     * Constructor
     */
    public function __construct()
    {
        $db = new \App\Model\Database\Query();

        $this->data = $db->select('app.settings.all()');
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
        if (is_null($key))
        {
            return $this->data;
        }

        return $this->data[$key] ?? '';
    }
}
