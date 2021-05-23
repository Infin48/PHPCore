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

namespace Model\System;

/**
 * SystemUrl
 */
class SystemUrl
{    
    /**
     * @var array $pages Page URLs
     */
    protected static array $pages = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        if (!self::$pages) {
            self::$pages = json_decode(file_get_contents(ROOT . '/Includes/Settings/URL.json'), true);
        }
    }

    /**
     * Returns translated page parameter
     * 
     * @return string
     */
    public function getPage()
    {
        $pages = array_merge(self::$pages['default'], self::$pages['hidden']);

        return isset($pages['/page-']) ? substr($pages['/page-'], 1, 1) : 'page';
    }

    /**
     * Translates URL
     *
     * @param  string $url
     * @param  bool $hidden If true - uses also hidden URL
     * 
     * @return string
     */
    public function translate( string $url, bool $hidden = true )
    {
        $pages = $hidden ? array_merge(array_flip(self::$pages['default']), array_flip(self::$pages['hidden'])) : array_flip(self::$pages['default']);

        return str_ireplace(array_keys($pages), array_values($pages), '/' . trim($url, '/') . '/');
    }

    /**
     * Builds url
     *
     * @param  string $url
     * 
     * @return string
     */
    public function build( string $url )
    {
        $_url = $url;
        if ($url === '/' or empty($url)) {
            return '/';
        }
        
        $url = '/' . implode('/', array_filter(explode('/', $url))) . '/';
        
        $url = $this->translate($url, false);
        $url = str_ireplace(array_keys(self::$pages['default']), array_values(self::$pages['default']), $url);

        if (preg_match('/[#]/', $url)) {
            $url = rtrim($url, '/');
        }

        $parse = parse_url($_url);

        if (isset($parse['scheme'])) {
            $url = ltrim($url, '/');
        }

        if (isset($parse['fragment'])) {
            $url = rtrim($url, '/');
        }

        return $url;
    }
}
