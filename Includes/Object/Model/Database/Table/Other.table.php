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

namespace App\Table;

/**
 * Other
 */
class Other extends Table
{    
    /**
     * Returns version of database
     *
     * @return string
     */
    public function version()
    {
        return $this->db->query('SELECT VERSION() AS version')['version'];
    }
}