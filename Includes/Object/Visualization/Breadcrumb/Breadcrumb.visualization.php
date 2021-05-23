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

namespace Visualization\Breadcrumb;

/**
 * Breadcrumb
 */
class Breadcrumb extends \Visualization\Visualization 
{    
    /**
     * Adds href value to data
     *
     * @param  string $href
     * 
     * @return void
     */
    public function href( string $href )
    {
        $this->obj->set->data('href', $href);
    }
}