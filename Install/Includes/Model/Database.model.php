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
    public \PDO $connect;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $allow = json_decode(file_get_contents(ROOT . '/Install/Includes/Settings.json'), true);
        if ((bool)$allow['db'] === false) return false;

        $access = json_decode(file_get_contents(ROOT . '/Includes/Settings/.htdata.json'), true);

        if ($access['name'] && $access['host'] && $access['user'] && $access['pass']) {

            // CONNECT
            $this->connect = @new \PDO('mysql:dbname=' . $access['name'] . ';host=' . $access['host'] . ';port=' . $access['port'] . ';charset=utf8mb4', $access['user'], $access['pass'], [
                \PDO::ATTR_ERRMODE               => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES      => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND    => 'SET NAMES utf8',
            ]);
            $this->query('SET NAMES utf8');
        }

    }
    
    /**
     * Executes query
     *
     * @param  string $query The query
     * @param  array $param Parameters
     * 
     * @return void
     */
    public function query( string $query, array $param = [] )
    {
        $row = $this->connect->prepare($query);
        $row->execute($param);
    }
}