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

namespace App\Model\File\Type;

/**
 * Zip
 */
class Zip extends \App\Model\File\Form
{
    /**
     * @var array $formats Allowed formats
     */
    public array $formats = ['application/x-zip-compressed', 'application/zip', 'application/x-zip', 'application/x-rar-compressed'];

    /**
     * @var int $size Max file size
     */
    public int $size = 500000;
}