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
 * Language
 */
class Language
{
    /**
     * @var array $language Language
     */
    private static array $language = [];

    /**
     * Loads language
     *
     * @param  string $language Name of language
     * 
     * @return void
     */
    public function load( string $language = '' )
    {
        if (!$language)
        {
            if (!static::$language)
            {
                new \App\Model\Language('cs');
            }
            return;
        }

        $name = $language;

        $JSON = new \App\Model\JSON('/Languages/' . $name . '/Info.json');

        if (!$JSON->exists())
        {
            throw new \App\Exception\System('Searched language "' . $language . '" does not exist!');
        }

        foreach ($JSON->get('tree')['install'] ?? [] as $file)
        {
            require ROOT . '/Languages/' . $name . '/Install' . $file;
            static::$language = array_merge(static::$language, $language);
        }
    }

    /**
     * Returns given key from language
     *
     * @param  string|null $string If null - returns whole language
     * 
     * @return mixed
     */
    public function get( string $key = null )
    {
        if (is_null($key))
        {
            return static::$language;
        }

        $keys = preg_split('/(?<=[a-zA-Z0-9])[.]/', $key);
        $return = static::$language;
        foreach ($keys as $_key)
        {
            $return = $return[str_replace('\\.', '.', $_key)] ?? '';
        }

        return $return;
    }
}