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
        if (isset($_POST['key']) and $_POST['key'] == SESSION_ID) {
            if (isset($_POST[$button])) {
                return true;
            }
        }

        return false;
    }
}
