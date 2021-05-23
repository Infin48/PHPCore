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

use Model\Database\Query;

/**
 * Block
 */
abstract class Block {
    
    /**
     * @var array Pagination data
     */
    public array $pagination;

    /**
     * @var \Block\BlockJoin $join BlockJoin
     */
    protected \Block\BlockJoin $join;

    /**
     * @var \Block\BlockSelect $select BlockSelect
     */
    protected \Block\BlockSelect $select;

    /**
     * @var \Model\Database\Query $db Database
     */
    protected \Model\Database\Query $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new Query();
        $this->join = new BlockJoin();
        $this->select = new BlockSelect();
    }
}