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
     * Returns value from data
     *
     * @param  string $key If null - returns whole object data
     * 
     * @return mixed
     */
    public function data( string $key = null )
    {
        if (is_null($key)) {
            return $this->object->object['data'] ?? [];
        }

        if ($key === 'convert') {
            return $this->object->object['data']['convert'] ?? [];
        }

        return $this->object->object['data'][$key] ?? '';
    }

    /**
     * Returns value from options
     *
     * @param  string $key If null - returns whole object options
     * 
     * @return mixed
     */
    public function options( string $key = null )
    {
        if (is_null($key)) {
            return $this->object->object['options'] ?? [];
        }

        return $this->object->object['options'][$key] ?? '';
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
        return $this->object->object['options']['template'][$key] ?? '';
    }

    /**
     * Returns convert data from child default object
     * 
     * @return array
     */
    public function convert()
    {
        return $this->object->object['body']['default']['data']['convert'] ?? [];
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
            return $this->object->object['data']['button'] ?? [];
        }

        return $this->object->object['data']['button'][$key] ?? '';
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
            return $this->object->object['body'] ?? [];
        }

        return $this->object->object['body'][$key] ?? [
            'options' => [],
            'data' => []
        ];
    }

    /**
     * Returns position of searched object
     * 
     * @param string $objectName Name of searched objecty
     * 
     * @return int
     */
    public function position( string $objectName )
    {
        $key = array_search($objectName, array_keys($this->body()));

        if ($key === false) {
            throw new \Exception\System('Hledaný objekt s názvem \'' . $objectName . '\' nebyl nalezen!');
        }

        return $key;
    }
}