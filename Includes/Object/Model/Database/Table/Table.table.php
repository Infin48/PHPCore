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
 * Block
 */
abstract class Table {
    
    /**
     * @var array Pagination data
     */
    public array $pagination = [
        'offset' => 0,
        'max' => 99999
    ];

    /**
     * @var \App\Table\TableJoin $join BlockJoin
     */
    protected \App\Table\TableJoin $join;

    /**
     * @var \App\Table\TableSelect $select BlockSelect
     */
    protected \App\Table\TableSelect $select;

    /**
     * @var \App\Model\Database\Query $db Database
     */
    protected \App\Model\Database\Query $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new \App\Model\Database\Query();
        $this->join = new \App\Table\TableJoin();
        $this->select = new \App\Table\TableSelect();
    }
}