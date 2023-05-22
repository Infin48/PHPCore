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
 * Text
 */
class Text
{
    /**
     * @var string $file Content of file
     */
    private string $file;

    /**
     * @var string $path Path to file
     */
    private string $path;

    /**
     * @var bool $exists True if file exists
     */
    private bool $exists = false;

    /**
     * Constructor
     *
     * @param  string $path Path to file
     */
    public function __construct( string $path )
    {
        if (!str_starts_with($path, 'http://') and !str_starts_with($path, 'https://'))
        {
            $path = ROOT . $path;
        }

        if (!file_exists($path))
        {
            return;
        } 
        
        $this->exists = true;
        $this->path = $path;
        $this->file = file_get_contents($path, false, CONTEXT);
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
     * Returns content of file
     * 
     * @return string
     */
    public function get()
    {
        return $this->file;
    }

    /**
     * Set data to file
     *
     * @param  string $key Key
     * @param  mixed $value Value
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $this->file = str_replace('{' . $key . '}', $value, $this->file);
    }
    
    /**
     * Saves file
     * 
     * @param  string $path
     * 
     * @return void
     */
    public function save( string $path = null )
    {
        file_put_contents(ROOT . ($path ?: $this->path), $this->file);
    }
}