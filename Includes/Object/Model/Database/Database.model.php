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

use Model\Database\QueryCompiler;

/**
 * Database
 */
class Database
{
    /**
     * @var \PDO $connect PDO
     */
    protected static $connect;

    /**
     * @var int $id Last inserted ID
     */
    protected int $id;

    /**
     * @var array $options PDO options
     */
    private array $options = [
        \PDO::ATTR_ERRMODE               => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_EMULATE_PREPARES      => false,
        \PDO::MYSQL_ATTR_INIT_COMMAND    => 'SET NAMES utf8mb4',
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        if (!isset(self::$connect)) {
            try {

                $access = json_decode(@file_get_contents(ROOT . '/Includes/.htdata.json'), true);

                // CONNECT
                self::$connect = @new \PDO('mysql:dbname=' . $access['name'] . ';host=' . $access['host'] . ';port=' . $access['port'] . ';charset=utf8mb4', $access['user'], $access['pass'], $this->options);

            } catch (\Exception $e) {
                throw new \Exception\System('Nepodařilo se připojit k databázi! ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Destroys database connection
     *
     * @return void
     */
    public static function destroy()
    {
        self::$connect = null;
    }

    /**
     * Returns last inserted ID
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->id ?? self::$connect->lastInsertId();
    }
    
    /**
     * Executes compiled query
     *
     * @param  string $query
     * @param  array $param
     * 
     * @return object
     */
    protected function execute( string $query, array $param = [] )
    {
        try {
            $row = self::$connect->prepare($query);
            $row->execute($param);

            return $row;

        } catch ( \Exception $e ) {
            throw new \Exception\System($e->getMessage() . '<br>' . $query);
        }
    }

    /**
     * Compiles query
     *
     * @param  string $tableName
     * @param  array $query
     * @param  string $type Query type
     * @param  int $id Item Id
     * 
     * @return string Compiled query
     */
    protected function compile( string $tableName, array $query, string $type, int $id = null )
    {
        $compiler = new QueryCompiler($tableName, $query, $type, $id);
        return $this->execute($compiler->getQuery(), $compiler->getParams());
    }
}