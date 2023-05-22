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

namespace App\Visualization\Form;

/**
 * Form
 */
class Form
{
    /**
     * @var array $object Object
     */
    private array $object = [];

    /**
     * Constructor
     *
     * @param  string|array $format Path to format
     */
    public function __construct( string $format )
    {
        $JSON = new \App\Model\JSON('/Install/Includes/Object/Visualization/Form/Formats' . $format);
        if (!$JSON->exists())
        {
            throw new \App\Exception\System('Formát ' . $format . ' nebyl nalezen!');
        }
        
        $this->object = $JSON->get();
    }

    /**
     * Sets event on success
     *
     * @param  object $page Page object
     * @param  string $method Method name
     * 
     * @return void
     */
    public function callOnSuccess( object $page, string $method )
    {
        $this->object['options'] = [
            'page' => $page,
            'method' => $method
        ];

        return $this;
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
        if (is_null($key))
        {
            return $this->object;
        }

        $keys = preg_split('/(?<=[a-zA-Z0-9-_\/\[\]])[.]/', $key);

        $return = $this->object;
        foreach ($keys as $_)
        {
            $return = $return[str_replace('\\', '', $_)] ?? '';
        }

        return $return;
    }
}