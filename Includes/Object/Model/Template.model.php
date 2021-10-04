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

/**
 * Template
 */
class Template
{
    /**
     * @var array $data Template data
     */
    private static array $data;

    /**
     * Constructor
     * 
     * @param string $template Template name
     * @param string $templateInitial Default system template
     * @param string $path Template path
     */
    public function __construct( string $template = null, string $templateInitial = null, string $path = null )
    {
        if ($template and $path) {

            self::$data = json_decode(file_get_contents(ROOT . $path . '/' . $template . '/Info.json'), true);
            
            self::$data['template'] = $template;
            self::$data['templateInitial'] = $templateInitial;
            self::$data['path'] = $path;
        }
    }

    /**
     * Returns path to template file
     *
     * @param  string $path
     * 
     * @throws \Exception\System If given file is not found
     * 
     * @return string
     */
    public function template( string $path )
    {
        
        if ($path === 'Error.phtml' and isset(self::$data['templateInitial'])) {
            
            self::$data['template'] = self::$data['templateInitial'];
            self::$data['path'] = '/Styles';

            unset(self::$data['templateInitial']);
            return $this->template('Error.phtml');
        }
        
        if (str_starts_with($path, '$')) {
            $path = array_values(array_filter(explode('/', $path)));
            array_shift($path);
            
            $path = '/Plugins/' . ($plugin = array_shift($path)) . '/Styles/Templates/' . implode('/', $path);
            
            if (file_exists(ROOT . $path)) {
                return ROOT . $path;
            }
            
            throw new \Exception\System('Stránka vyžaduje nexistující vzhled z pluginu \'' . $plugin .  '\' s cestou \'' . $path . '\'');
        }
        
        $paths = [
            ROOT . self::$data['path'] . '/' . self::$data['template'] . '/Templates/' . ltrim($path, '/'),
            ROOT . self::$data['path'] . '/Default/Templates/' . ltrim($path, '/')
        ];
        
        foreach ($paths as $_path) {
            
            if (file_exists($_path)) {
                return $_path;
            }
        }

        throw new \Exception\System('Stránka vyžaduje nexistující vzhled ' . $path . ' s cestou \'' . self::$data['path'] . '\'');
    }

    /**
     * Returns path to theme file
     *
     * @param  string $path
     * 
     * @throws \Exception\System If given file is not found
     * 
     * @return string
     */
    public function theme( string $path )
    {
        if (file_exists(ROOT . ($path = self::$data['path'] . '/' . self::$data['template'] . '/Themes' . $path))) {
            return $path;
        }
        throw new \Exception\System('Hledaný vzhledový prvek nebyl nalezen: ' . $path); 
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
        return self::$data[$key] ?? '';
    }
}