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

namespace Process\Admin\Synchronize;

/**
 * Scripts
 */
class Scripts extends \Process\ProcessExtend
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
        $this->db->table(TABLE_SETTINGS, [
            'session.scripts' => RAND
        ]);

        // ADD RECORD TO LOG
        $this->log();

        // REFRESH PAGE
        $this->refresh();
    }
}