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

namespace App\Exception;

/**
 * Notice
 */
class Notice extends \Exception
{
    /**
     * Constructor
     *
     * @param string $notice Notice
     */

    public function __construct( string $notice )
    {
        global $router;
        $router->notice($notice);
        exit();
    }
}