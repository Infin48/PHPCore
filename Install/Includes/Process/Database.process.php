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

use Model\JSON;
use Model\Database as DatabaseModel;

/**
 * Database
 */
class Database extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'host'      => [
                'type' => 'text',
                'required' => true
            ],
            'user_name'  => [
                'type' => 'text',
                'required' => true
            ],
            'user_password'  => [
                'type' => 'text'
            ],
            'database'  => [
                'type' => 'text',
                'required' => true
            ],
            'port'      => [
                'type' => 'number'
            ]
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $port = $this->data->get('port') ?: 3306;

        try {
            // TEST CONNECTION
            new \PDO('mysql:host=' . $this->data->get('host') . ';port=' . $port . ';dbname=' . $this->data->get('database'), $this->data->get('user_name'), $this->data->get('user_password'));

            $JSON = new JSON('/Includes/.htdata.json');
            $JSON->set('host', $this->data->get('host'));
            $JSON->set('user', $this->data->get('user_name'));
            $JSON->set('pass', $this->data->get('user_password'));
            $JSON->set('name', $this->data->get('database'));
            $JSON->set('port', $port);
            $JSON->save();
        
        } catch (\PDOException $e) {
            throw new \Exception\Notice($e->getMessage());
        }

        $JSON = new JSON('/Install/Includes/Settings.json');


        if ($JSON->get('operation') === 'install') {

            $db = new DatabaseModel(true);
            $db->file('/Install/Query.sql');
        }

        $JSON->set('db', true);

        if ($JSON->get('operation') === 'install') {
            $JSON->set('page', 'install-admin');
        } else {
            $JSON->set('page', 'update');
        }
        $JSON->set('back', false);
        $JSON->save();
    }
}