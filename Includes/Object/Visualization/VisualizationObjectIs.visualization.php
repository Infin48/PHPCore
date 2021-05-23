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

namespace Visualization;

/**
 * VisualizationObjectIs
 */
class VisualizationObjectIs
{
    /**
     * @var array $object Object
     */
    protected array $object = [];

    /**
     * Constructor
     *
     * @param  array $object
     */
    public function __construct( array $object )
    {
        $this->object = $object;
    }

    /**
     * Checks if data exists
     *
     * @param  string $key
     * 
     * @return bool
     */
    public function data( string $key )
    {
        return isset($this->object['data'][$key]);
    }

    /**
     * Checks if options exists
     *
     * @param  string $key
     * 
     * @return bool
     */
    public function options( string $key )
    {
        return isset($this->object['options'][$key]);
    }

    /**
     * Checks if template exists
     *
     * @param  string $key
     * 
     * @return bool
     */
    public function template( string $key )
    {
        return isset($this->object['options']['template'][$key]);
    }

    /**
     * Checks if convert data from default object exists
     * 
     * @return bool
     */
    public function convert()
    {
        return isset($this->object['body']['default']['data']['convert']);
    }

    /**
     * Checks if body or object in body exists
     * 
     * @param string $key If null - checks if body exists
     * 
     * @return bool
     */
    public function body( string $key = null )
    {
        if (is_null($key)) {
            return isset($this->object['body']);
        }

        return isset($this->object['body'][$key]);
    }
}