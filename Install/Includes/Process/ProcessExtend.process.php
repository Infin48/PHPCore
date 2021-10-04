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

use Model\Database;

use Process\ProcessData;

/**
 * ProcessExtend
 */
class ProcessExtend
{    
    /**
     * @var \Model\Database $db Database
     */
    public \Model\Database $db;

    /**
     * @var \Process\ProcessData $data ProcessData
     */
    protected \Process\ProcessData $data;

    /**
     * @var \Process\ProcessCheck $check ProcessCheck
     */
    protected \Process\ProcessCheck $check;
        
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->check = new ProcessCheck();
    }
    
    /**
     * Loads data to process
     *
     * @param  array $data The data
     * 
     * @return void
     */
    public function data( array $data )
    {
        $this->data = new ProcessData($data);
    }
}