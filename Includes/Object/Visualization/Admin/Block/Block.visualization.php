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

namespace Visualization\Admin\Block;

/**
 * Block
 */
class Block extends \Visualization\Visualization
{
    /**
     * Sets link to block
     *
     * @param  string $link Link
     * 
     * @return $this
     */
    public function href( string $link )
    {
        $this->obj->set->data('href', '$' . $link);

        return $this;
    }
}
