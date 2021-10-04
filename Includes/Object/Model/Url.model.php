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

namespace Model;

use Block\Settings;

/**
 * Url
 */
class Url extends \Model\Model
{
    /**
     * @var array $ID Loaded ID from URL
     */
    private array $ID = [];

    /**
     * @var array $URL URL
     */
    private array $URL = [];

    /**
     * @var string $URLcurrent Current page URL
     */
    private static string $URLcurrent = '';

    /**
     * @var array $pages Page URLs
     */
    private static array $pages = [];

    /**
     * @var array $parsedURL Parsed URL
     */
    private array $parsedURL = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $settings = new Settings();

        $default = $settings->getURLDefault();
        $hidden = $settings->getURLHidden();

        self::$pages = [
            'default' => array_combine(array_column($default, 'settings_url_from'), array_column($default, 'settings_url_to')),
            'hidden' => array_combine(array_column($hidden, 'settings_url_from'), array_column($hidden, 'settings_url_to'))
        ];

        $i = 0;
        $parsedURL = self::translate(urldecode($_SERVER['REQUEST_URI']));
        $parsedURL = array_values(array_filter(explode('/', $parsedURL)));
        if (($parsedURL[0] ?? '') === 'admin') {
            array_shift($parsedURL);
        }
        
        foreach ($parsedURL as $parameter) {
            
            $ex = explode('.', $parameter);
            if (ctype_digit($parameter) or ctype_digit($ex[0])) {
                array_push($this->ID, $parameter);
                unset($parsedURL[$i]);
                $i++;
                continue;
            }
            
            $_ex = explode('-', $parameter);

            if (count($_ex) > 1) {

                foreach (explode('.', $_ex[1]) as $item) {

                    $split = array_values(array_filter(preg_split('/(?<=[a-zA-Z])(?=[0-9]+)/i', $item)));

                    if (!isset($split[1])) {
                        $this->parsedURL[$_ex[0]] = trim($split[0]);
                    } else {

                        $this->parsedURL[$_ex[0]] ??= [];

                        $this->parsedURL[$_ex[0]] = array_merge($this->parsedURL[$_ex[0]], [trim($split[0]) =>trim($split[1])]);
                    }
                }
            } else {
                $this->parsedURL[trim($_ex[0])] = '';
                array_push($this->URL, trim($_ex[0]));
            }
            
            $i++;
        }

        if (in_array($this->getPage(), array_keys($this->parsedURL)) and !empty($this->parsedURL[$this->getPage()])) {
            define('PAGE', $this->parsedURL[$this->getPage()]);
        } else {
            define('PAGE', 1);
        }

        if (in_array($this->getTab(), array_keys($this->parsedURL)) and !empty($this->parsedURL[$this->getTab()])) {
            define('TAB', $this->parsedURL[$this->getTab()]);
        } else {
            define('TAB', '');
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
     * Returns translated tab parameter
     * 
     * @return string
     */
    public function getTab()
    {
        $pages = array_merge(self::$pages['default'], self::$pages['hidden']);

        return isset($pages['/tab-']) ? substr($pages['/tab-'], 1, 1) : 'tab';
    }

    /**
     * Translates URL
     *
     * @param  string $url URL
     * @param  bool $hidden If true - uses also hidden URL
     * 
     * @return string
     */
    public static function translate( string $url, bool $hidden = true )
    {
        $pages = $hidden ? array_merge(array_flip(self::$pages['default']), array_flip(self::$pages['hidden'])) : array_flip(self::$pages['default']);
        
        return str_ireplace(array_keys($pages), array_values($pages), '/' . trim($url, '/') . '/');
    }

    /**
     * Builds url
     *
     * @param  string $url URL
     * @param  bool $append Tf true - $url will be appened to current URL
     *  
     * @return string
     */
    public static function build( string $url, bool $append = false )
    {
        if ($append === true) {
            $url = self::getURL() . '/' . $url;
        }

        $_url = $url;
        if ($url === '/' or empty($url)) {
            return '/';
        }
        if (str_starts_with($url, '#')) {
            return $url;
        }
        
        $url = '/' . implode('/', array_filter(explode('/', $url))) . '/';
        $url = self::translate($url, false);
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

    /**
     * Checks if parameter in URL exists
     *
     * @param string $parameter Prameter name
     * 
     * @return bool
     */
    public function is( string $parameter )
    {
        return isset($this->parsedURL[$parameter]);
    }
    
    /**
     * Returns value from URL parameter
     *
     * @param string $parameter Prameter name
     * 
     * @return string
     */
    public function get( string $parameter = null )
    {
        if (is_null($parameter)) {
            return $this->URL;
        }


        return $this->parsedURL[$parameter] ?? '';
    }

    /**
     * Returns ID from URL
     * 
     * @param int $number Number of ID
     * @param int $shorten If true - returned ID will be shortened
     *
     * @return string|int
     */
    public function getID( int $number = 0, bool $shorten = true )
    {
        if ($shorten === true) {
            return explode('.', $this->ID[$number] ?? 0)[0];
        }

        return $this->ID[$number] ?? 0;
    }

    /**
     * Returns ID list
     *
     * @return array
     */
    public function getAllID()
    {
        return $this->ID;
    }

    /**
     * Adds ID to list
     * 
     * @param int $number The ID
     *
     * @return void
     */
    public function addID( mixed $ID )
    {
        array_push($this->ID, $ID);
    }

    /**
     * Shifts an element off the beginning of URL
     * 
     * @return string Shifted element
     */
    public function shift()
    {
        return array_shift($this->URL);
    }

    /**
     * Returns first element of URL
     * 
     * @return string
     */
    public function getFirst()
    {
        return $this->URL[0] ?? '';
    }

    /**
     * Returns current page URL
     * 
     * @return string
     */
    public static function getURL()
    {
        return self::$URLcurrent;
    }

    /**
     * Changes current page URL
     * 
     * @param  string $URL New URL
     * 
     * @return void
     */
    public static function setURL( string $URL )
    {
        self::$URLcurrent = $URL;
    }
}
