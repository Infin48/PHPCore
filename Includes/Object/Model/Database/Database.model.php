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

namespace App\Model\Database;

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
     * @var \App\Model\Data $data Data
     */
    protected \App\Model\Data $data;

    /**
     * @var \App\Model\Database\QueryCompiler $compiler Query compiler
     */
    private static \App\Model\Database\QueryCompiler $compiler;

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
    public function __construct( \App\Model\Data $data = null )
    {
        if ($data)
        {
            $this->data = $data;
        }

        if (!isset(self::$connect))
        {
            self::$connect = '';

            if (!file_exists(ROOT . '/Includes/.htdata.json'))
            {
                return;
            }

            try {

                $access = json_decode(@file_get_contents(ROOT . '/Includes/.htdata.json'), true);

                // Connect
                self::$connect = @new \PDO('mysql:dbname=' . $access['name'] . ';host=' . $access['host'] . ';port=' . $access['port'] . ';charset=utf8mb4', $access['user'], $access['pass'], $this->options);

                self::$compiler = new \App\Model\Database\QueryCompiler();

            } catch (\Exception $e) {
                throw new \App\Exception\System('Nepodařilo se připojit k databázi! ' . $e->getMessage());
            }
        }
    }

    /**
     * Returns true if application is connected with database
     *
     * @return void
     */
    public function isConnected()
    {
        if (isset(self::$connect))
        {
            if (!empty(self::$connect))
            {
                return true;
            }
        }

        return false;
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
            throw new \App\Exception\System($e->getMessage() . '<br>' . $query);
        }
    }

    /**
     * Compiles query
     *
     * @param  string $tableName
     * @param  array|string $query
     * @param  string $type Query type
     * @param  int|string $id Item ID
     * @param  int $flag Additional flags
     * 
     * @return string Compiled query
     */
    protected function compile( string $table, string $type, array|string $query = null, int|string $id = null, int $flag = null )
    {
        self::$compiler->compile(table: $table, type: $type, query: $query, id: $id, flag: $flag);
        return $this->execute(self::$compiler->getQuery(), self::$compiler->getParams());
    }
}