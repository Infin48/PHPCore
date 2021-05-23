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

namespace Model\Database;

/**
 * Query
 */
class Query extends Database
{
    /**
     * Executes query
     *
     * @param  string $query The query
     * @param  array $parameters
     * @param  int $catchType
     * 
     * @return array
     */
    public function query( string $query, array $parameters = [], int $catchType = SINGLE )
    {
        $result = $this->execute($query, $parameters);
        if ($catchType === ROWS) return $result->fetchAll();

        return $result->fetch();
    }
    
    /**
     * Update query
     *
     * @param  string $table Table name
     * @param  array $query The query
     * @param int $id Item ID
     * 
     * @return void
     */
    public function update( string $table, array $query, int $id = null )
    {
        $this->compile($table, $query, 'update', $id);
    }

    /**
     * Insert query
     *
     * @param  string $table Table name
     * @param  array $query The query
     * 
     * @return void
     */
    public function insert( string $table, array $query )
    {
        $this->compile($table, $query, 'insert');
        $this->id = self::$connect->lastInsertId();
    }
}