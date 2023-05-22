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

namespace App\Model\File;

/**
 * JSON
 */
class JSON
{
    /**
     * @var array $JSON Content of JSON
     */
    private array $JSON = [];

    /**
     * @var string $path Path to JSON
     */
    private string $path;

    /**
     * @var bool $exists True if file exists
     */
    private bool $exists = false;

    /**
     * Constructor
     *
     * @param  string $path Path to JSON
     */
    public function __construct( string $path )
    {
        if (!str_starts_with($path, 'http://') and !str_starts_with($path, 'https://') and !str_contains($path, ROOT))
        {
            $path = ROOT . $path;
        }

        if (!str_starts_with($path, 'http://') and !str_starts_with($path, 'https://'))
        {
            if (!file_exists($path))
            {
                return;
            }
        }

        $this->exists = true;
        $this->path = $path;
        $this->JSON = json_decode(@file_get_contents($path, false, CONTEXT), true) ?? [];
    }

    /**
     * Set data to JSON
     *
     * @param  mixed $path Key
     * @param  mixed $value Value
     * 
     * @return void
     */
    public function set( mixed $key, mixed $value = null )
    {
        if (is_null($value))
        {
            $this->JSON = $key;
            return;
        }
        $path = '';
        $keys = explode('.', $key);
        foreach ($keys as $key) {
            $path .= '[\'' . $key . '\']';   
        }

        eval('$this->JSON' . $path . ' = $value;');
    }

    /**
     * Returns content of JSON file
     * 
     * @param  string $key Key
     * 
     * @return array
     */
    public function get( string $key = null )
    {
        if (is_null($key))
        {
            return $this->JSON;
        }

        $keys = preg_split('/(?<=[a-zA-Z0-9])[.]/', $key);
        
        $return = $this->JSON;
        foreach ($keys as $_key) {
            $return = $return[str_replace('\\', '', $_key)] ?? '';
        }

        return $return;
    }

    /**
     * Returns true if file exists otherwise false
     * 
     * @return bool
     */
    public function exists()
    {
        return $this->exists;
    }
    
    /**
     * Saves JSON
     * 
     * @return void
     */
    public function save()
    {
        file_put_contents($this->path, json_encode($this->JSON, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}