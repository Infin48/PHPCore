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
 * Data
 */
class Data 
{
    /**
     * @var array $data Page data
     */
    public array $data = [];

    /**
     * Adds data to page
     *
     * @param array $data
     * 
     * @return void
     */
    public function data( array $data )
    {
        $this->data = array_merge($this->data, $data);
    }
}
