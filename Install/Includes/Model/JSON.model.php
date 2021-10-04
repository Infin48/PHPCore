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
     * @var bool $exist True if JSON file exists
     */
    private bool $exist;

    /**
     * Constructor
     *
     * @param  string $path Path to JSON
     */
    public function __construct( string $path )
    {
        $this->path = $path;

        if (file_exists(ROOT . $path)) {

            $this->JSON = json_decode(file_get_contents(ROOT . $path), true);
            $this->exist = true;
        } else {

            $this->exist = false;
        }
    }

    /**
     * Set data to JSON
     *
     * @param  string $key Key
     * @param  mixed $value Value
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $this->JSON[$key] = $value;
    }

    /**
     * Returns true if JSON file exists
     * 
     * @return bool
     */
    public function exist()
    {
        return $this->exist;
    }

    /**

     * Checks if is set key in JSON
     *
     * @param  string $key Key
     * 
     * @return bool
     */
    public function is( string $key )
    {
        return isset($this->JSON[$key]);
    }

    /**
     * Returns content of JSON file or key value
     * 
     * @param string $key Key
     * 
     * @return mixed
     */
    public function get( string $key = null )
    {
        if (!is_null($key)) {
            return $this->JSON[$key] ?? '';
        }

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