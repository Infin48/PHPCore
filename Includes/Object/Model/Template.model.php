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
 * Template
 */
class Template
{
    /**
     * @var array $data Template data
     */
    private static array $data = [];

    /**
     * Constructor
     * 
     * @param string $template Template name
     * @param string $templateInitial Default system template
     * @param string $path Template path
     */
    public function __construct( string $template = null, string $path = null )
    {
        if ($template or $path)
        {
            self::$data['path'] = $path;
            self::$data['template'] = $template;

            // Get template settings from json
            $JSON = new \App\Model\File\JSON(self::$data['path'] . '/' . $template . '/Info.json');

            // Save tempalte data to variable
            self::$data = array_merge(self::$data, $JSON->get());
        }
    }

    /**
     * Returns value from template settings
     *
     * @param  string $key The key
     * 
     * @return mixed
     */
    public function get( string $key )
    {
        $keys = explode('.', $key);
        $return = self::$data;
        foreach ($keys as $_key)
        {
            if (!isset($return[$_key]))
            {
                return '';
            }

            $return = $return[$_key];
        }

        return $return;
    }
}