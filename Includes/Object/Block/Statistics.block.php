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

namespace Block;

/**
 * Statistics
 */
class Statistics extends Block
{    
    /**
     * Returns data from statistics
     *
     * @return array
     */
    public function getAll()
    {
        $stats = $this->db->query('SELECT * FROM ' . TABLE_STATISTICS, [], ROWS);

        $data = [];
        foreach ($stats as $stat) {
            $data[$stat['key']] = $stat['value'];
        }
        return $data;
    }
}