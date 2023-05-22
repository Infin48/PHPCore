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
 * Misc
 */
class Misc extends \App\Model\File\Form
{
    /**
     * @var array $formats Allowed formats
     */
    public array $formats = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/svg+xml',
        'image/vnd.microsoft.icon',
        'image/bmp',
        'image/gif',

        'text/html',
        'text/css',
        'text/plain',

        // Archives
        'application/x-zip',
        'application/x-msdownload',
        'application/x-rar-compressed',
        'application/x-zip-compressed',
        'application/zip',

        'application/vnd.oasis.opendocument.text',
        'application/xml',
        'application/json',
        'application/msword',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel.template.macroEnabled.12',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/pdf',

        'audio/mpeg',
        'audio/wma',
        'audio/mp3',

        'video/webm',
        'video/mp4'
    ];

    /**
     * @var int $size Max file size
     */
    public int $size = 500000;
}