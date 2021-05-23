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

namespace Exception;

/**
 * Notice
 */
class Notice extends \Exception
{
    /**
     * Constructor
     *
     * @param string $notice
     * @param array $assign
     */
    public function __construct( string $notice, array $assign = [] )
    {
        global $router;
        $router->notice($notice, $assign);
        exit();
    }
}