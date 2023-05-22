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

namespace App\Table;

/**
 * Settings
 */
class Settings extends Table
{    
    /**
     * Returns PHPCore settings
     *
     * @return array
     */
    public function all()
    {
        $stats = $this->db->query('SELECT * FROM ' . TABLE_SETTINGS, [], ROWS);

        $data = [];
        foreach ($stats as $stat) {
            $data[$stat['key']] = $stat['value'];
        }
        return $data;
    }

    /**
     * Returns PHPCore default URLs
     *
     * @return array
     */
    public function URLDefault()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_SETTINGS_URL . ' WHERE settings_url_hidden = 0', [], ROWS);
    }

    /**
     * Returns PHPCore hidden URLs
     *
     * @return array
     */
    public function URLHidden()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_SETTINGS_URL . ' WHERE settings_url_hidden = 1', [], ROWS);
    }
}