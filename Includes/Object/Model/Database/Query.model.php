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
        $result = $this->execute(str_replace(array("\n", "\r"), '', $query), $parameters);
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

    /**
     * Updates table with plugin settings
     *
     * @param  string $plugin Plugin name
     * @param  array $query The query
     * 
     * @return void
     */
    public function plugin( string $plugin, array $query )
    {
        $key = array_key_first($query);
        $value = $query[array_key_first($query)];
        array_shift($query);

        $table = 'phpcore_plugins_' . strtolower($plugin);
        $join = '';
        $set = 'SET p0.value = ?';
        
        $i = 1;
        foreach ($query as $_key => $_value) {
            $join .= 'LEFT JOIN ' . $table . ' p' . $i . ' ON p' . $i . '.key = "' . $_key . '" ';
            $set .= ', p' . $i . '.value = ?'; 
            
            $i++;
        }
        
        
        $this->execute('
            UPDATE phpcore_plugins_' . strtolower($plugin) . ' p0
            ' . $join . '
            ' . $set . '
            WHERE p0.key = "' . $key . '"
        ', array_merge([$value], array_values($query)));
    }

    /**
     * Updates statistics table
     *
     * @param  array $query The query
     * 
     * @return void
     */
    public function stats( array $query )
    {
        $table = TABLE_STATISTICS;
        $short = explode(' ', $table)[1];

        $join = '';
        $set = [];
        
        $i = 0;
        foreach ($query as $_key => $_value) {

            if ($i !== 0) {
                $join .= 'LEFT JOIN ' . $table . $i . ' ON ' . $short . $i . '.key = "' . $_key . '" ';
            }

            if ($_value < 0) {
                array_push($set, $short . $i . '.value = ' . $short . $i . '.value - ' . abs((int)$_value));
            } else {
                array_push($set, $short . $i . '.value = ' . $short . $i . '.value + ' . (int)$_value);
            }
            
            $i++;
        }
        
        $this->execute('
            UPDATE ' . $table . '0
            ' . $join . '
            SET ' . implode(',', $set) . '
            WHERE ' . $short . '0.key = "' . array_key_first($query) . '"
        ');
    }

    /**
     * Updates table
     *
     * @param  string $tableName Table name
     * @param  array $query The query
     * 
     * @return void
     */
    public function table( string $tableName, array $query )
    {
        $table = $tableName;
        $short = explode(' ', $table)[1];

        $join = '';
        $set = [];
        
        $i = 0;
        foreach ($query as $_key => $_value) {

            if ($i !== 0) {
                $join .= 'LEFT JOIN ' . $table . $i . ' ON ' . $short . $i . '.key = "' . $_key . '" ';
            }

            if ($_value < 0) {
                array_push($set, $short . $i . '.value = ?');
            } else {
                array_push($set, $short . $i . '.value = ?');
            }
            
            $i++;
        }
        
        $this->execute('
            UPDATE ' . $table . '0
            ' . $join . '
            SET ' . implode(',', $set) . '
            WHERE ' . $short . '0.key = "' . array_key_first($query) . '"
        ', array_values($query));
    }
}