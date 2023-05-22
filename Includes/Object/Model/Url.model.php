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
 * Url
 */
class Url
{
    /**
     * @var array $ID Loaded ID from URL
     */
    private static array $ID = [];

    /**
     * @var array $param URL
     */
    private static array $param = [];

    /**
     * @var array $URL Current url built by pages
     */
    public static array $URL = [];

    /**
     * @var array $URL Loaded URL
     */
    public static array $URLs = [];

    /**
     * @var array $pages Page URLs
     */
    private static array $pages = [];

    /**
     * @var array $parsedURL Parsed URL
     */
    public static array $parsedURL = [];

    /**
     * @var array $URLHidden Hidden URLs
     */
    private array $URLHidden = [];

    /**
     * @var array $parsedURL Default URLs
     */
    private array $URLDefault = [];
    
    /**
     * Constructor
     */
    public function __construct( array $URLDefault, array $URLHidden )
    {
        $this->URLDefault = $URLDefault;
        $this->URLHidden = $URLHidden;
    }

    /**
     * Parses url
     * 
     * @return void
     */
    public function parseURL()
    {
        self::$pages = [
            'default' => array_combine(array_column($this->URLDefault, 'settings_url_from'), array_column($this->URLDefault, 'settings_url_to')),
            'hidden' => array_combine(array_column($this->URLHidden, 'settings_url_from'), array_column($this->URLHidden, 'settings_url_to'))
        ];

        $parsedURL = self::translateFromURL(urldecode($_SERVER['REQUEST_URI']));
        $parsedURL = self::$parsedURL = array_values(array_filter(explode('/', $parsedURL)));

        if (($parsedURL[0] ?? '') === 'admin')
        {
            array_shift($parsedURL);
        }
        
        foreach ($parsedURL as $param)
        {
            $ex = explode('.', $param);
            if (preg_match('~[0-9]+~', $ex[0]) or ctype_digit($ex[0]))
            {
                if (!str_contains($ex[0], '-'))
                {
                    array_push(self::$ID, (int)$param);
                    continue;
                }
            }
            
            array_push(self::$URLs, $param);
            if (preg_match('~[-]+~', $param))
            {
                $dash = explode('-', $param);
                self::$param[$dash[0]] = $dash[1];
                
                if (preg_match('~[.]+~', $dash[1]))
                {
                    self::$param[$dash[0]] = [];
                    
                    foreach(explode('.', $dash[1]) as $dot)
                    {
                        $split = array_values(array_filter(preg_split('/(?<=[a-zA-Z])(?=[0-9]+)/i', $dot)));
                        
                        self::$param[$dash[0]][$split[0]] = $split[1];
                    }
                }
            }
        }
        
        define('PAGE', $this->get('page') ?: 1);
        define('TAB', $this->get('tab') ?: '');
    }

    /**
     * Translates URL
     *
     * @param  string $url URL
     * 
     * @return string
     */
    public static function translateToURL( string $url )
    {
        $pages = self::$pages['default'];

        $url = '/' . trim($url, '/') . '/';

        foreach ($pages as $original => $translated)
        {
            if ($url == $original)
            {
                return $translated;
            }
        }

        return str_ireplace(array_keys($pages), array_values($pages), $url);
    }

    /**
     * Translates URL
     *
     * @param  string $url URL
     * 
     * @return string
     */
    public static function translateFromURL( string $url )
    {
        $pages = array_merge(self::$pages['default'], self::$pages['hidden']);

        $url = '/' . trim($url, '/') . '/';

        foreach ($pages as $original => $translated)
        {
            if ($url == $translated)
            {
                return $original;
            }
        }

        return str_ireplace(array_values($pages), array_keys($pages), $url);
    }

    /**
     * Builds url
     *
     * @param  string $url URL
     *  
     * @return string
     */
    public static function build( string $url = null, string $tab = null )
    {
        if (str_starts_with($url, '~'))
        {
            $url = self::getURL() . '/' . mb_substr($url, 1);
        }

        if (!is_null($tab))
        {
            $url = self::getURL(false) . '/tab-' . $tab . '/';
        }

        $_url = $url;
        if ($url === '/' or empty($url)) {
            return '/';
        }
        if (str_starts_with($url, '#')) {
            return $url;
        }
        
        $url = '/' . implode('/', array_filter(explode('/', $url))) . '/';
        $url = self::translateToURL($url);
        $url = str_ireplace(array_keys(self::$pages['default']), array_values(self::$pages['default']), $url);

        if (preg_match('/[#]/', $url)) {
            $url = rtrim($url, '/');
        }

        $parse = parse_url($_url);

        if (isset($parse['scheme']))
        {
            $url = ltrim($url, '/');
        }

        if (isset($parse['fragment']))
        {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    /**
     * Checks if parameter in URL exists
     *
     * @param string $parameter Prameter name
     * 
     * @return bool
     */
    public function is( string $parameter )
    {
        return isset(self::$param[$parameter]);
    }
    
    /**
     * Returns value from URL parameter
     *
     * @param string|int $parameter Prameter name
     * 
     * @return string|array
     */
    public function get( string|int $parameter = null )
    {
        if (is_null($parameter))
        {
            return self::$URLs;
        }

        if (is_int($parameter))
        {
            return self::$URLs[$parameter - 1] ?? '';
        }

        $keys = explode('.', $parameter);
        $return = self::$param;
        
        foreach ($keys as $key)
        {
            if (!isset($return[$key])) {
                return '';
            }

            $return = $return[$key];
        }

        return $return;
    }

    /**
     * Returns ID from URL
     * 
     * @param int $ID Number of ID
     * @param int $reduce If true - returned ID will be reduced
     *
     * @return string|int
     */
    public function getID( int $ID = 0, bool $reduce = true )
    {
        if ($reduce === true)
        {
            return explode('.', self::$ID[$ID] ?? 0)[0];
        }

        return self::$ID[$ID] ?? 0;
    }

    /**
     * Returns all loaded ID from URL
     * 
     * @return array
     */
    public function getIDs()
    {
        $IDs = [];
        foreach (self::$ID as $ID)
        {
            array_push($IDs, explode('.', $ID)[0]);
        }

        return $IDs;
    }

    /**
     * Shifts an element off the beginning of URL
     * 
     * @return string Shifted element
     */
    public function shift()
    {
        return array_shift(self::$URLs);
    }

    /**
     * Returns first element of URL
     * 
     * @return string
     */
    public function getFirst()
    {
        return self::$URLs[0] ?? '';
    }

    /**
     * Returns last element of URL
     * 
     * @return string
     */
    public function getLast()
    {
        if (count(self::$URLs) - 1 < 0)
        {
            return '';
        }
        return self::$URLs[count(self::$URLs) - 1];
    }

    /**
     * Returns current page URL
     * 
     * @param  bool $withTAB If true - returned URL will be returned with tab if contains
     * 
     * @return string
     */
    public static function getURL( bool $withTAB = true )
    {
        $URL = self::$URL;

        if ($withTAB === false)
        {
            foreach ($URL as &$param)
            {
                if (str_starts_with($param, 'tab-'))
                {
                    $param = '';
                }
            }
        }

        // Filter array
        $filter = array_filter($URL);

        // If array is empty
        if (empty($filter))
        {
            return '/';
        }

        return '/' . implode('/', $filter) . '/';
    }

    /**
     * Changes current page URL
     * 
     * @param  string $URL New URL
     * 
     * @return void
     */
    public function set( string $URL )
    {
        self::$URL = array_values(array_filter(explode('/', self::build($URL))));
    }
}
