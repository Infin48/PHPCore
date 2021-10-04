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
 * VisualizationObjectDelete
 */
class VisualizationObjectDelete
{
    /**
     * @var \Visualization\VisualizationObject $object Object
     */
    public \Visualization\VisualizationObject $object;

    /**
     * Constructor
     *
     * @param  \Visualization\VisualizationObject $object
     */
    public function __construct( \Visualization\VisualizationObject $object )
    {
        $this->object = $object;
    }

    /**
     * Deletes value from data
     *
     * @param  string $key
     * 
     * @return void
     */
    public function data( string $key )
    {
        unset($this->object->object['data'][$key]);
    }

    /**
     * Deletes value from options
     *
     * @param  string $key
     * 
     * @return void
     */
    public function options( string $key )
    {
        unset($this->object->object['options'][$key]);
    }

    /**
     * Deletes button
     *
     * @param  string $key If null - deletes all buttons
     * 
     * @return void
     */
    public function button( string $key = null )
    {
        if (is_null($key)) {
            unset($this->object->object['data']['button']);
            return;
        }
        unset($this->object->object['data']['button'][$key]);
    }

    /**
     * Deletes object from body
     *
     * @param  string $key
     * 
     * @return void
     */
    public function body( string $key )
    {
        unset($this->object->object['body'][$key]);
    }

    /**
     * Deletes current object
     * 
     * @return void
     */
    public function delete()
    {
        $this->object->object = [];
    }
}