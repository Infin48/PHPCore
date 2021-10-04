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
 * VisualizationObjectSetBody
 */
class VisualizationObjectSetBody
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
     * Adds object after given object
     *
     * @param  string $afterObjectName Name of searched object
     * @param  array $body Body of new object
     * 
     * @return void
     */
    public function after( string $afterObjectName, array $body )
    {
        $key = array_search($afterObjectName, array_keys($this->object->get->body()));
        $this->object->set->body(
            array_slice($this->object->get->body(), 0, $key + 1) + $body + array_slice($this->object->get->body(), $key + 1)
        );
    }

    /**
     * Adds object before given object
     *
     * @param  string $beforeObjectName Name of searched object
     * @param  array $body Body of new object
     * 
     * @return void
     */
    public function before( string $beforeObjectName, array $body = null )
    {
        $key = array_search($beforeObjectName, array_keys($this->object->get->body()));
        $this->object->set->body(
            array_slice($this->object->get->body(), 0, $key) + $body + array_slice($this->object->get->body(), $key)
        );
    }
}