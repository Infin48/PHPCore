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

use Exception;

/**
 * ProcessData
 */
class ProcessData
{
    /**
     * @var array $data Process data
     */
    private array $data;

    /**
     * Constructor
     *
     * @param  array $data
     */
    public function __construct( array $data )
    {
        $this->data = $data;
    }
    
    /**
     * Returns value from data
     *
     * @param  string $input
     * 
     * @throws \Exception\System If given input will not set
     * 
     * @return mixed
     */
    public function get( string $input )
    {
        if (array_key_exists($input, (array)$this->data) === false) {
            throw new Exception(get_class($this) . ' zkouší získat neexistující proměnnou \'' . $input . '\'');
        }

        return $this->data[$input];
    }

    /**
     * Checks if given form radio or checkbox is checked
     *
     * @param  string $string The input name
     * 
     * @return bool
     */
    public function is( string $string )
    {
        return (int)$this->data[$string] === (int)1 ? true : false;
    }
}