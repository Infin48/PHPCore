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
 * Query
 */
class Query extends Database
{
    /**
     * Executes file with SQL
     *
     * @param  string $file File
     * 
     * @return void
     */
    public function file( string $file )
    {
        $this->execute(file_get_contents(ROOT . $file));
    }

    /**
     * Select query
     *
     * @param  string $path Path to table and method
     * @param  array $params Params for method
     * 
     * @return mixed
     */
    public function select( string $table, ...$params )
    {
        $explode = explode('.', $table);
        $method = mb_substr(array_pop($explode), 0, -2);

        $prefix = '\App';
        if ($explode[0] != 'app')
        {
            $prefix = '\Plugin\\' . ucfirst(array_shift($explode)); 
        } else array_shift($explode);

        foreach ( $explode as &$_ )
        {
            $_e = explode('-', $_);
            foreach ( $_e as &$__ )
            {
                $__ = ucfirst($__);
            }

            $_ = implode('', $_e);
        }
        if (count($explode) == 2)
        {
            $explode[2] = $explode[1];
            $explode[1] = $explode[0];
            $explode[0] = 'Plugin';
        }
        
        $table = $prefix . '\Table\\' . implode('\\', $explode);
        $table = new $table();

        if (isset($this->data) and isset($this->data->pagination) and !empty($this->data->pagination))
        {
            $table->pagination = $this->data->pagination;
        } else $table->pagination = ['offset' => 0, 'max' => 9999999];

        return match (count($params))
        {
            0 => $table->{$method}(),
            1 => $table->{$method}($params[0]),
            2 => $table->{$method}($params[0], $params[1]),
            3 => $table->{$method}($params[0], $params[1], $params[2])
        };
    }

    public function moveOnePositionUp( string $table, string $id = null )
    {
        if (is_null($id))
        {
            $this->execute('UPDATE ' . $table . ' SET position_index = position_index + 1');
            return;
        }

        $compiler = new \App\Model\Database\QueryCompiler();
        $primaryKey = $compiler->getKeys()[$table];

        $short = explode(' ', $table)[1];

        $this->execute('
            UPDATE ' . $table . '
            LEFT JOIN ' . $table . '2 ON ' . $short . '2.position_index = ' . $short . '.position_index + 1
            SET ' . $short . '.position_index = ' . $short . '.position_index + 1,
                ' . $short . '2.position_index = ' . $short . '2.position_index - 1
            WHERE ' . $short . '.' . $primaryKey . ' = ? AND ' . $short . '2.' . $primaryKey . ' IS NOT NULL
        ', [$id]);
    }

    public function moveOnePositionDown( string $table, string $id = null )
    {
        $compiler = new \App\Model\Database\QueryCompiler();
        $primaryKey = $compiler->getKeys()[$table];

        $short = explode(' ', $table)[1];

        $this->execute('
            UPDATE ' . $table . '
            LEFT JOIN ' . $table . '2 ON ' . $short . '2.position_index = ' . $short . '.position_index - 1
            SET ' . $short . '.position_index = ' . $short . '.position_index - 1,
                ' . $short . '2.position_index = ' . $short . '2.position_index + 1
            WHERE ' . $short . '.' . $primaryKey . ' = ? AND ' . $short . '2.' . $primaryKey . ' IS NOT NULL
        ', [$id]);
    }

    /**
     * Adds record to log
     * 
     * @param string $name Name of action(operation)
     * @param string $text Usually some name of forum, group, user etc...
     *
     * @return void
     */
    public function addToLog( string $name, string $text = '' )
    {
        $this->insert(TABLE_LOG, [
            'user_id'       => LOGGED_USER_ID,
            'log_text'      => $text,
            'log_action'    => $name
        ]);
    }

    /**
     * Sends notification to user
     * 
     * @param string $name Name of action(operation)
     * @param int $ID ID of item
     * @param int $to User ID
     * @param bool $replace If true and user will have unreaded same notification, time will be updated otherwise will be added another notification to user
     *
     * @return void
     */
    public function sendNotification( string $name, int $ID, int $to, bool $replace = false )
    {
        if (LOGGED_USER_ID == $to)
        {
            return;
        }

        $notifiID = '';
        if ($replace === true)
        {
            $notifiID = $this->query('
                SELECT user_notification_id
                FROM ' . TABLE_USERS_NOTIFICATIONS . '
                WHERE user_notification_item = ? AND user_notification_item_id = ? AND user_id = ?
            ', [$name, $ID, LOGGED_USER_ID])['user_notification_id'] ?? '';
            
            // Notification already exists
            if ($notifiID)
            {
                // Update time
                $this->update(TABLE_USERS_NOTIFICATIONS, [
                    'user_notification_created' => DATE_DATABASE
                ], $notifiID);

                return;
            }
        }

        // Add new notification
        $this->insert(TABLE_USERS_NOTIFICATIONS, [
            'user_id'                       => LOGGED_USER_ID,
            'to_user_id'                    => $to,
            'user_notification_item'        => $name,
            'user_notification_item_id'     => $ID
        ]);
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
        $result = $this->execute(str_replace(['\n', '\r'], '', $query), $parameters);
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
        $this->compile(table: $table, query: $query, type: 'update', id: $id);
    }

    /**
     * Insert query
     *
     * @param  string $table Table name
     * @param  array $query The query
     * @param  int $flag Additional flags
     * 
     * @return void
     */
    public function insert( string $table, array $query, int $flag = null )
    {
        $this->compile(table: $table, query: $query, type: 'insert', flag: $flag);
        $this->id = self::$connect->lastInsertId();
    }

    /**
     * Update query
     *
     * @param  string $table Table name
     * @param  string $key Key to WHERE condition
     * @param  int|string $id Item ID
     * 
     * @return void
     */
    public function delete( string $table, string $key = null, int|string $id = null )
    {
        $this->compile(table: $table, query: $key, type: 'delete', id: $id);
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