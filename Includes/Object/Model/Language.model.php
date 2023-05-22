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
     * @var array $settings Language settings
     */
    private array $settings = [];

    /**
     * @var array $language Language
     */
    private array $language = [];

    /**
     * @var \App\Plugin\Plugin $plugin Plugin instance for loading languages from plugins
     */
    private \App\Plugin\Plugin $plugin;

    /**
     * Constructor
     * 
     * @param \Plugin\Plugin $plugin Plugin instance for loading languages from installed plugins
     */
    public function __construct( \App\Plugin\Plugin $plugin = null )
    {
        if (is_null($plugin))
        {
            return;
        }

        $this->plugin = $plugin;
    }

    /**
     * Changes language
     *
     * @param string $key Key
     * @param string $value Value
     * 
     * @return void
     */
    public function set( string $key, mixed $value )
    {
        $brackets = '';
        $keys = preg_split('/(?<=[a-zA-Z0-9-_\/\[\]])[.]/', $key);
        foreach ($keys as $_key)
        {
            $brackets .= '[\'' . str_replace('\.', '.', $_key) . '\']';
        }

        eval('$this->language' . $brackets . ' = $value;');
    }

    /**
     * Loads language
     *
     * @param string $language Name of language (cs, en, sk, ...)
     * @param string $folder Folder of language (admin/website/install)
     * 
     * @return mixed
     */
    public function load( string $language, \App\Model\Template $template, string $folder )
    {
        $JSON = new \App\Model\File\JSON('/Languages/' . $language . '/Info.json');
        $this->settings = $JSON->get();

        $list = [];

        $lang = $language;

        foreach ($this->settings['tree'][$folder] as $file)
        {
            require ROOT . '/Languages/' . $lang . '/' . ucfirst($folder) . $file;
            $list = array_merge($list, $language);
        }

        // File model
        $file = new \App\Model\File\File();

        if (!defined('LIST_OF_INSTALLED_PLUGINS'))
        {
            $this->language = $list;
            return;
        }

        foreach (LIST_OF_INSTALLED_PLUGINS as $item)
        {
            $pluginLanguage = $this->plugin->get($item . '.language');

            // If plugin language does not exist
            if (!$file->exists('/Plugins/' . $item . '/Languages/' . $pluginLanguage . '/Info.json'))
            {
                continue;
            }
            $JSON = new \App\Model\File\JSON('/Plugins/' . $item . '/Languages/' . $pluginLanguage . '/Info.json');
            foreach ($JSON->get('tree.' . $folder) ?: [] as $_file)
            {
                $path = '/Plugins/' . $item . '/Languages/' . $pluginLanguage . '/' . ucfirst($folder) . $_file;

                // If language file does not exist
                if (!$file->exists($path))
                {
                    continue;
                }
                
                require ROOT . $path;

                if (isset($language['L_PERMISSION']['L_CATEGORY']))
                {
                    $list['L_PERMISSION']['L_CATEGORY'] = array_merge($language['L_PERMISSION']['L_CATEGORY'], $list['L_PERMISSION']['L_CATEGORY']);
                }
                if (isset($language['L_PERMISSION']['L_LIST']))
                { 
                    $list['L_PERMISSION']['L_LIST'] = array_merge($language['L_PERMISSION']['L_LIST'], $list['L_PERMISSION']['L_LIST']);
                }
                if (isset($language['L_PERMISSION']['L_DESC']))
                {
                    $list['L_PERMISSION']['L_DESC'] = array_merge($language['L_PERMISSION']['L_DESC'], $list['L_PERMISSION']['L_DESC']);
                }

                if (isset($language['L_NOTICE']['L_FAILURE']))
                {
                    $list['L_NOTICE']['L_FAILURE'] = array_merge($language['L_NOTICE']['L_FAILURE'], $list['L_NOTICE']['L_FAILURE']);
                }

                if (isset($language['L_NOTICE']['L_SUCCESS']))
                {
                    $list['L_NOTICE']['L_SUCCESS'] = array_merge($language['L_NOTICE']['L_SUCCESS'], $list['L_NOTICE']['L_SUCCESS']);
                }

                if (isset($language['L_WINDOW']['L_DESC']))
                {
                    $list['L_WINDOW']['L_DESC'] = array_merge($language['L_WINDOW']['L_DESC'], $list['L_WINDOW']['L_DESC']);
                }

                if (isset($language['L_TITLE']))
                {
                    foreach ($language['L_TITLE'] as $key => $value)
                    {
                        $list['L_TITLE']['\Plugin\\' . $item . $key] = $value;
                    }
                }

                $list[$item] = $language;
            }
        }

        $list['Style'] = [];

        $path = '/Styles/' . $template->get('template');

        $_path = $path . '/Languages/' . $template->get('language');
        // If template language exist
        if ($file->exists($path . '/Languages/' . $lang))
        {
            $_path = $path . '/Languages/' . $lang;
        }

        $JSON = new \App\Model\File\JSON($_path . '/Info.json');
        foreach ($JSON->get('tree.' . $folder) ?: [] as $_file)
        {
            $path = $_path . '/' . ucfirst($folder) . $_file;

            // If language file does not exist
            if (!$file->exists($path))
            {
                continue;
            }
            
            require ROOT . $path;

            $list['Style'] = array_merge($list['Style'], $language);
        }

        $this->language = $list;
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
            return $this->language;
        }

        $keys = preg_split('/(?<=[a-zA-Z0-9])[.]/', $key);
        $return = $this->language ?? [];
        foreach ($keys as $_key)
        {
            $return = $return[str_replace('\\.', '.', $_key)] ?? '';
        }

        return $return;
    }
}