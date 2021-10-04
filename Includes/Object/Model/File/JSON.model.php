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
 * JSON
 */
class JSON
{
    /**
     * @var array $JSON Content of JSON
     */
    private array $JSON;

    /**
     * @var string $path Path to JSON
     */
    private string $path;

    /**
     * Constructor
     *
     * @param  string $path Path to JSON
     */
    public function __construct( string $path )
    {
        $this->path = $path;
        $this->JSON = json_decode(file_get_contents(ROOT . $path), true);
    }

    /**
     * Set data to JSON
     *
     * @param  string $path Key
     * @param  mixed $value Value
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $this->JSON[$key] = $value;
    }

    /**
     * Returns content of JSON file
     * 
     * @return array
     */
    public function get()
    {
        return $this->JSON;
    }
    
    /**
     * Saves JSON
     * 
     * @return void
     */
    public function save()
    {
        file_put_contents(ROOT . $this->path, json_encode($this->JSON, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}