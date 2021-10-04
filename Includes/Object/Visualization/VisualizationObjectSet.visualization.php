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
 * VisualizationObjectSet
 */
class VisualizationObjectSet
{
    /**
     * @var \Visualization\VisualizationObject $object Object
     */
    private \Visualization\VisualizationObject $object;

    /**
     * @var \Visualization\VisualizationObjectSetBody $object Object
     */
    public \Visualization\VisualizationObjectSetBody $body;

    /**
     * Constructor
     *
     * @param  \Visualization\VisualizationObject $object
     */
    public function __construct( \Visualization\VisualizationObject $object )
    {
        $this->body = new VisualizationObjectSetBody($object);
        $this->object = $object;
    }

    /**
     * Sets value to object
     *
     * @param  string $key
     * @param  mixed $value
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $this->object->object[$key] = $value;
    }
    
    /**
     * Sets value to data
     *
     * @param  string|array $key
     * @param  mixed $value
     * 
     * @return void
     */
    public function data( string|array $key, mixed $value = null )
    {
        if (is_null($value)) {
            $this->object->object['data'] = $key;
            return;
        }

        $this->object->object['data'][$key] = $value;
    }

    /**
     * Sets value to options
     *
     * @param  string $key
     * @param  mixed $value
     * 
     * @return void
     */
    public function options( string $key, mixed $value )
    {
        $this->object->object['options'][$key] = $value;
    }

    /**
     * Sets value to template
     *
     * @param  string $key
     * @param  mixed $value
     * 
     * @return void
     */
    public function template( string $key, mixed $value = null )
    {
        if (is_null($value)) {
            $this->object->object['options']['template'] = $key;
            return;
        }

        $this->object->object['options']['template'][$key] = $value;
    }

    /**
     * Sets data to button
     *
     * @param  string $key
     * @param  mixed $value
     * 
     * @return void
     */
    public function button( string $key, mixed $value )
    {
        $this->object->object['data']['button'][$key] = $value;
    }

    /**
     * Adds object to body
     *
     * @param  string|array $key
     * @param  array $value
     * 
     * @return void
     */
    public function body( string|array $key, array $value = null )
    {
        if (is_null($value)) {
            $this->object->object['body'] = $key;
            return;
        }

        $this->object->object['body'][$key] = $value;
    }

    /**
     * Sets data to notice
     *
     * @param  string $key
     * @param  mixed $value
     * 
     * @return void
     */
    public function notice( string $key, mixed $value )
    {
        $this->object->object['data']['notice'][$key] = $value;
    }
}