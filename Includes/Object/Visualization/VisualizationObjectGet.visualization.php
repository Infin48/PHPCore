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
 * VisualizationObjectGet
 */
class VisualizationObjectGet
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
     * Returns value from data
     *
     * @param  string $key If null - returns whole object data
     * 
     * @return mixed
     */
    public function data( string $key = null )
    {
        if (is_null($key)) {
            return $this->object['data'] ?? [];
        }

        return $this->object['data'][$key] ?? '';
    }

    /**
     * Returns value from options
     *
     * @param  string $key
     * 
     * @return mixed
     */
    public function options( string $key )
    {
        return $this->object['options'][$key] ?? '';
    }

    /**
     * Returns template
     *
     * @param  string $key
     * 
     * @return mixed
     */
    public function template( string $key )
    {
        return $this->object['options']['template'][$key] ?? '';
    }

    /**
     * Returns convert data from child default object
     * 
     * @return array
     */
    public function convert()
    {
        return $this->object['body']['default']['data']['convert'] ?? [];
    }

    /**
     * Returns button
     *
     * @param  string $key If null - returns all buttons
     * 
     * @return mixed
     */
    public function button( string $key = null )
    {
        if (is_null($key)) {
            return $this->object['data']['button'] ?? [];
        }

        return $this->object['data']['button'][$key] ?? '';
    }

    /**
     * Returns object body or object from body
     * 
     * @param string $key If null - returns whole body
     * 
     * @return array
     */
    public function body( string $key = null )
    {
        if (is_null($key)) {
            return $this->object['body'] ?? [];
        }

        return $this->object['body'][$key] ?? [];
    }
}