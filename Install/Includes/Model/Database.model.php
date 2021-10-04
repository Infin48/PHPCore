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

namespace Model;

/**
 * Database
 */
class Database
{
    /**
     * @var \PDO $connect PDO
     */
    private static $connect;

    /**
     * Constructor
     * 
     * @param bool $direct
     */
    public function __construct( bool $direct = false )
    {
        if ($direct === false) {

            $allow = json_decode(file_get_contents(ROOT . '/Install/Includes/Settings.json'), true);

            if ((bool)$allow['db'] === false) return false;
        }
        $access = json_decode(file_get_contents(ROOT . '/Includes/.htdata.json'), true);

        if (isset($access['name']) && isset($access['host']) && isset($access['user']) && isset($access['port'])) {

            self::$connect = @new \PDO('mysql:dbname=' . $access['name'] . ';host=' . $access['host'] . ';port=' . $access['port'] . ';charset=utf8mb4', $access['user'], $access['pass'], [
                \PDO::ATTR_ERRMODE               => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES      => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND    => 'SET NAMES utf8',
            ]);

            $this->query('SET NAMES utf8');
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
        $row = self::$connect->prepare($query);
        $row->execute($parameters);

        if ($catchType === ROWS) return $row->fetchAll();

        return $row->fetch();
    }

    /**
     * Executes file with SQL
     *
     * @param  string $file File
     * 
     * @return void
     */
    public function file( string $file )
    {
        self::$connect->exec(file_get_contents(ROOT . $file));
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
        foreach ($query as $key => $value) {
            $row = self::$connect->prepare('UPDATE ' . $tableName . ' SET `value` = ? WHERE `key` = ?;');
            $row->execute([$value, $key]);
        }
    }
}