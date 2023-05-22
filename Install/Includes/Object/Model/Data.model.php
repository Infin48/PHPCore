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

namespace App\Model;

/**
 * Data
 */
class Data 
{
    /**
     * @var array $data Page data
     */
    public array $data = [];

    /**
     * @var array $breadcrumb Breadcrumb
     */
    public array $breadcrumb = [];

    public function __construct()
    {
        $this->data = [
            'data' => [
                'head' => [
                    'title' => '',
                    'description' => '',
                    'keyWords' => ''
                ]
            ],
            'inst' => [
                'language' => new \App\Model\Language()
            ]
        ];
    }

    /**
     * Returns searched value from data
     * 
     * @param string $key Key
     *
     * @return mixed
     */
    public function get( string $key = null )
    {
        if (!$key)
        {
            return $this->data;
        } 
        
        $keys = preg_split('/(?<=[a-zA-Z0-9-_\/\[\]])[.]/', $key);

        $return = $this->data;
        foreach ($keys as $_)
        {
            $return = $return[str_replace('\\', '', $_)] ?? '';
        }

        return $return;
    }
    
    /**
     * Sets value to data
     *
     * @param  string $key Key
     * @param  mixed $value Value
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $brackets = '';
        $keys = preg_split('/(?<=[a-zA-Z0-9-_\/\[\]])[.]/', $key);
        foreach ($keys as $_)
        {
            $brackets .= '[\'' . str_replace('\.', '.', $_) . '\']';
        }

        eval('$this->data' . $brackets . ' = $value;');
    }
}