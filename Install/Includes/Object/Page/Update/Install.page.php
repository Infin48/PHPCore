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
 * Install
 */
class Install extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database $db )
    {
        $db = new \App\Model\Database(true);

        $db->file('/Install/Update.sql');

        $db->table(TABLE_SETTINGS, [
            'session_scripts' => mt_rand(),
            'session_labels' => mt_rand(),
            'session_groups' => mt_rand(),
            'site_template' => 'Default',
            'site_updated' => DATE_DATABASE,
        ]);

        echo json_encode([
            'status' => 'ok'
        ]);

        exit();
    }
}