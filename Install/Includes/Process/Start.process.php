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

namespace Process;

/**
 * Start
 */
class Start extends \Process\ProcessExtend
{    
    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $stats = json_decode(file_get_contents(ROOT . '/Includes/Settings/Statistics.json'), true);

        foreach ($stats as $key => $value) {
            $stats[$key] = 0;
        }

        file_put_contents(ROOT . '/Includes/Settings/Statistics.json', json_encode($stats, JSON_PRETTY_PRINT));

        $this->system->install([
            'db' => false,
            'page' => 1
        ]);
    }
}