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

namespace Model\File;

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
     * Constructor
     *
     * @param  string $path Path to file
     */
    public function __construct( string $path )
    {
        $this->path = $path;
        $this->file = file_get_contents(ROOT . $path);
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