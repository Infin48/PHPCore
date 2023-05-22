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

namespace App\Visualization\Breadcrumb;

/**
 * Breadcrumb
 */
class Breadcrumb extends \App\Visualization\Visualization 
{
    /**
     * @var array $translate List of keys which will be translated to default language
     */
    protected array $translate = [
        'body.?.data.title'
    ];

    /**
     * @var array $defaultValues List of keys and their default values
     */
    protected array $defaultValues = [];

    /**
     * @var array $parseToPath List of keys which their values will be parsed to path
     */
    protected array $parseToPath = [];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [
        'body.?.data.href'
    ];

    /**
     * @var string $languagePrefix Default language prefix
     */
    protected string $languagePrefix = 'L_BREADCRUMB';

    /**
     * Adds href value to data
     *
     * @param  string $href
     * 
     * @return $this
     */
    public function href( string $href )
    {
        $this->set('data.href', $href);

        return $this;
    }
}