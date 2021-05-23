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

namespace Model\Build;

/**
 * Build
 */
class Build extends \Model\Model
{  
    /**
     * @var \Model\Build\BuildUrl $url BuildUrl
     */
    public \Model\Build\BuildUrl $url;

    /**
     * @var \Model\Build\BuildDate $date BuildDate
     */
    public \Model\Build\BuildDate $date;

    /**
     * @var \Model\Build\BuildUser $user BuildUser
     */
    public \Model\Build\BuildUser $user;
    
    /**
     * Loads builders
     *
     * @return void
     */
    public function load()
    {
        $this->url = new BuildUrl();
        $this->date = new BuildDate();
        $this->user = new BuildUser();
        $this->user->url = $this->url;
    }
}