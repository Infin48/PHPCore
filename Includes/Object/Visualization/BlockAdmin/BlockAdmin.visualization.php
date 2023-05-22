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

namespace App\Visualization\BlockAdmin;

/**
 * BlockAdmin
 */
class BlockAdmin extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to language
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
     * Sets link to block
     *
     * @param  string $link Link
     * 
     * @return $this
     */
    public function href( string $link )
    {
        $this->set('data.href', '$' . $link);

        return $this;
    }
}
