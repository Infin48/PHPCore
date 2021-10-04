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

use \Model\Language;
use \Model\System;

/**
 * Model
 */
abstract class Model
{
    /**
     * @var \Model\Language $language Language
     */
    protected \Model\Language $language;

    /**
     * @var \Model\System $system System
     */
    protected \Model\System $system;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->system = new System();
        $this->language = new Language();
    }
}