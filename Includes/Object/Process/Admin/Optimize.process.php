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

namespace Process\Admin;

/**
 * Optimize
 */
class Optimize extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [];

    /**
     * @var array $options Process options
     */
    public array $options = [];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $result = $this->db->query('
            SHOW TABLE STATUS WHERE Data_free > 0
        ', [], ROWS);

        foreach ($result as $table) {
            $this->db->query('OPTIMIZE TABLE `' . $table['Name'] . '`');
        }

        // ADD RECORD TO LOG
        $this->log();

        // REFRESH PAGE
        $this->refresh();
    }
}