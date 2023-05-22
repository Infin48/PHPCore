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

namespace App\Visualization;

/**
 * VisualizationGenerate
 */
class VisualizationGenerate
{
    /**
     * @var array $object Object
     */
    public array $object = [];

    /**
     * @var string $name Object name
     */
    private string $name = '';

    /**
     * @var \App\Visualization\VisualizationObject $obj VisualizationObject
     */
    private \App\Visualization\VisualizationObject $obj;

    /**
     * Constructor
     *
     * @param  array $object Object
     */
    public function __construct( array $object, string $name = '' )
    {
        $this->name = str_replace('\\', '', $name);
        $this->object = $object;

        $this->obj = new \App\Visualization\VisualizationObject($this, '');
    }

    public function getObject()
    {
        return $this->object;
    }

    /**
     * Returns name of current object
     * 
     * @return string
     */
    public function getCurrentPositionName()
    {
        return $this->name;
    }

    /**
     * Returns value on given key
     * 
     * @param  string $key The key
     * 
     * @return mixed
     */
    public function get( string $key = null )
    {
        return $this->obj->get($key);
    }

    /**
     * Returns value on given key
     * 
     * @param  string $key The key
     * 
     * @return mixed
     */
    public function each( string $key, callable $function )
    {
        $_key = str_replace('!', '', $key);
        if (!is_array($this->obj->get($_key)))
        {
            return;
        }
        $names = [];
        foreach ($this->obj->get($_key) as $object => $data)
        {
            if ($this->obj->get($_key . '.' . $object . '.options.hide') == true)
            {
                if (!str_ends_with($key, '!'))
                {
                    continue;
                }
            }

            $object = preg_replace('/(?<=[a-zA-Z0-9-_\/])[.]/', '\.', $object);
            
            
            array_push($names, $object);
            if (!$this->obj->get($_key . '.' . $object))
            {
                continue;
            }

            $data = $this->obj->get($_key . '.' . $object);
            if (is_string($data))
            {
                $function($data);
                continue;
            }

            $function(
                new \App\Visualization\VisualizationGenerate($this->obj->get($_key . '.' . $object), $object)
            );

            if (isset($data['options']['end']))
            {
                foreach ($names as $name)
                {
                    $this->obj->delete('body.' . $name);
                }
                return;
            }
        }
    }
}