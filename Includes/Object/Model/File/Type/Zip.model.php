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

namespace Model\File\Type;

/**
 * Zip
 */
class Zip extends \Model\File\Form
{
    /**
     * @var array $formats Allowed image formats
     */
    public array $formats = ['application/x-zip-compressed', 'application/zip', 'application/x-zip'];

    /**
     * @var int $size Max image size
     */
    public int $size = 50000;
}