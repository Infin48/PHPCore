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

namespace Page;

/**
 * Database
 */
class Database extends Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {   
        $this->templateName = 'Database';

        // SETUP DATABASE
        $this->process->form(type: 'Database');
    }
}