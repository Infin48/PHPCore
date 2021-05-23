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
 * Form
 */
class Form
{
    /**
     * @var bool $direct Direct mode
     */
    private bool $direct = false;
    
    /**
     * Constructor
     *
     * @param  bool $direct
     */
    public function __construct( bool $direct = false )
    {
        $this->direct = $direct;
    }
    
    /**
     * Returns value from form
     *
     * @param  string $key
     * 
     * @return mixed
     */
    public function get( string $key )
    {
        return strip_tags($_POST[$key] ?? '');
    }
        
    /**
     * Returns all form data
     *
     * @return array
     */
    public function getData()
    {
        return $_POST;
    }

    /**
     * Checks if submit button was pressed
     *
     * @param string $button Button name
     * 
     * @return bool
     */
    public function isSend( string $button )
    {
        if ((isset($_POST['key']) and $_POST['key'] == SESSION_ID) or $this->direct === true) {
            if (isset($_POST[$button]) or $this->direct === true) {
                
                return true;
            }
        }

        return false;
    }
}
