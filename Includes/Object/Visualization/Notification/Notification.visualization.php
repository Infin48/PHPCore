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

namespace App\Visualization\Notification;

/**
 * Notification
 */
class Notification extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to default language
     */
    protected array $translate = [
        'body.?.data.title',
        'body.?.data.text',
        'body.?.data.button.?.text'
    ];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [
        'body.?.data.button.?.href'
    ];
}
