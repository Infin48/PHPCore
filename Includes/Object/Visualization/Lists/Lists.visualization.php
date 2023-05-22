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

namespace App\Visualization\Lists;

/**
 * Lists
 */
class Lists extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to language
     */
    protected array $translate = [
        'body.?.data.title',
        'body.?.data.empty',
        'body.?.data.big.title',
        'body.?.data.small.title',
        'body.?.data.medium.title'
    ];

    /**
     * @var array $defaultValues List of keys and their default values
     */
    protected array $defaultValues = [
        'body.?.data.empty' => '',
        'body.?.data.title' => '',
        'body.?.data.small.title' => '',
        'body.?.data.medium.title' => '',
        'body.?.data.desc' => '',
        'body.?.body.?.options.template.big' => '',
        'body.?.body.?.options.template.mdeium' => '',
        'body.?.body.?.options.template.small' => '',
        'body.?.body.?.options.disabled' => false,
        'body.?.body.?.options.selected' => false
    ];

    /**
     * @var array $parseToPath List of keys which their values will be parsed to path
     */
    protected array $parseToPath = [
        'body.?.body.?.options.template.big',
        'body.?.body.?.options.template.small',
        'body.?.body.?.options.template.medium'
    ];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [];
}