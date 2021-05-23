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
     * @var object $delete VisualiaztionObjectDelete
     */
    public VisualizationObjectDelete $delete;

    /**
     * Constructor
     *
     * @param  array $object
     */
    public function __construct( array $object )
    {
        $this->delete = new VisualizationObjectDelete($object);
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
        $this->delete->object[$key] = $value;
    }
    
    /**
     * Sets value to data
     *
     * @param  string $key
     * @param  mixed $value
     * 
     * @return void
     */
    public function data( string $key, mixed $value )
    {
        $this->delete->object['data'][$key] = $value;
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
        $this->delete->object['options'][$key] = $value;
    }

    /**
     * Sets value to template
     *
     * @param  string $key
     * @param  mixed $value
     * 
     * @return void
     */
    public function template( string $key, mixed $value )
    {
        $this->delete->object['options']['template'][$key] = $value;
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
        $this->delete->object['data']['button'][$key] = $value;
    }

    /**
     * Adds row to body
     *
     * @param  string $key
     * @param  array $value
     * 
     * @return void
     */
    public function body( string $key, array $value )
    {
        $this->delete->object['body'][$key] = $value;
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
        $this->delete->object['data']['notice'][$key] = $value;
    }
}