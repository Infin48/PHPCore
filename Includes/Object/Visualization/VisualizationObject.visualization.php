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
 * VisualizationObject
 */
class VisualizationObject
{
    /**
     * @var object $object Object
     */
    private object $object;
    
    /**
     * @var string $path Path
     */
    public string $path = '';

    /**
     * Sets object
     * 
     * @param array $object
     * @param string $path
     */
    public function __construct( object $object, string $path )
    {
        if ($path)
        {
            $this->path = $path . '.';
        }

        $this->object = $object;
    }

    /**
     * Returns path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns searched value from object
     * 
     * @param string  $key Key
     *
     * @return mixed
     */
    public function get( string $key = null )
    {
        $path = $this->path . $key;
        if (is_null($key))
        {
            if (!$this->path)
            {
                return $this->object->object ?? [];
            }

            if (str_ends_with($this->path, '.'))
            {
                $path = mb_substr($this->path, 0, -1);
            }
        }
        
        $keys = preg_split('/(?<=[a-zA-Z0-9-_\/\[\]])[.]/', $path);

        $return = $this->object->object ?? [];

        foreach ($keys as $_key)
        {
            $return = $return[str_replace('\\', '', $_key)] ?? '';
        }
        if (str_starts_with($key, 'body'))
        {
            if (empty($return))
            {
                return [];
            }
        }

        return $return;
    }
    
    /**
     * Sets value to object
     *
     * @param  string|array $key Key
     * @param  mixed $value Value
     * 
     * @return void
     */
    public function set( string|array $key, mixed $value = null )
    {
        $path = $this->path . $key;
        if (is_null($value) and is_array($key) and !$this->path)
        {
            $this->object->object = $key;
            return;
        }

        if (is_null($value))
        {
            $value = $key;
            $key = '';

            if (str_ends_with($this->path, '.'))
            {
                $path = mb_substr($this->path, 0, -1);
            }
        }

        $brackets = '';
        $keys = preg_split('/(?<=[a-zA-Z0-9-_\/\[\]])[.]/', $path);
        foreach ($keys as $_key)
        {
            $brackets .= '[\'' . str_replace('\.', '.', $_key) . '\']';
        }

        eval('$this->object->object' . $brackets . ' = $value;');
    }

    public function setAfter( string $object, string|int $name, array $data )
    {
        $i = 1;
        foreach ($this->get('body') as $k => $v)
        {
            if ($k == $object)
            {
                break;
            }
            $i++;
        }

        $this->set('body',
            array_slice($this->get('body'), 0, $i) + [$name => $data] + array_slice($this->get('body'), $i)
        );
    }

    /**
     * Deletes value from object
     *
     * @param  string|array $key
     * 
     * @return void
     */
    public function delete( string|array $key = null )
    {
        if (is_null($key))
        {
            if (!$this->path)
            {
                $this->object->object = [];
                return;
            }
        }

        if (is_array($key))
        {
            foreach($key as $_)
            {
                $this->obj->delete($_);
            }

            return;
        }

        $_key = $this->path . $key;
        if (is_null($key))
        {
            $_key = trim($this->path, '.');
        }

        $path = '';
        $keys = preg_split('/(?<=[a-zA-Z0-9-_\/\[\]])[.]/', $_key);

        foreach ($keys as $key)
        {
            $path .= '[\'' . str_replace('\.', '.', $key) . '\']';
        }

        eval('unset($this->object->object' . $path . ');');
    }

    private function createObject()
    {
        $list = [];
        foreach ($this->list as $_)
        {
            $list = array_merge($list, ['body', preg_replace('/(?<=[a-zA-Z0-9-_\/])[.]/', '\.', $_)]);
        }
        $data = $this->get(implode('.', $list), true);
        
        if (!$data or !is_array($data))
        {
            throw new \App\Exception\System('Hledaný objekt nebyl nalezen! Cesta: ' . implode('.', $list));
        }

        $this->currentPositionObject = $data;
    }
}