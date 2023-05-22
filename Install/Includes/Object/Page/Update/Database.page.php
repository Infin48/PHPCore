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

namespace App\Page\Update;

/**
 * Database
 */
class Database extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = '/Database.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database $db )
    {
        $JSON = new \App\Model\JSON('/Install/Includes/Settings.json');
        $JSON->set('operation', 'update');
        $JSON->save();
        
        $form = new \App\Visualization\Form\Form('/Database.json');
        $form->callOnSuccess($this, 'setupDatabase');
        $data->form = $form;
    }

    public function setupDatabase( \App\Model\Data $data, \App\Model\Database $db, \App\Model\Post $post )
    {
        $port = $post->get('port') ?: 3306;

        try {
            // Test connection
            new \PDO('mysql:host=' . $post->get('host') . ';port=' . $port . ';dbname=' . $post->get('database'), $post->get('user_name'), $post->get('user_password'));

            $JSON = new \App\Model\JSON('/Includes/.htdata.json');
            $JSON->set('host', $post->get('host'));
            $JSON->set('user', $post->get('user_name'));
            $JSON->set('pass', $post->get('user_password'));
            $JSON->set('name', $post->get('database'));
            $JSON->set('port', $port);
            $JSON->save();
        
        } catch (\PDOException $e) {
            throw new \App\Exception\Notice($e->getMessage());
        }

        $JSON = new \App\Model\JSON('/Install/Includes/Settings.json');
        if ($JSON->get('operation') === 'install')
        {
            $db = new \App\Model\Database(true);
            $db->file('/Install/Query.sql');
        }

        $JSON->set('db', true);
        $JSON->save();

        if ($JSON->get('operation') === 'install')
        {
            redirect('/install/admin/');
        }

        redirect('/update/installing/');
    }
}