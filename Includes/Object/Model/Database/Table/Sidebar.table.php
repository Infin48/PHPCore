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
 * Sidebar
 */
class Sidebar extends Table
{   
    /**
     * Returns all buttons 
     *
     * @return array
     */
    public function all()
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_SIDEBAR . '
            ORDER By position_index DESC
        ', [], ROWS);
    }

    /**
     * Returns sidebar
     *
     * @param  string $ID Sidebar
     * 
     * @return array
     */
    public function get( string $ID )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_SIDEBAR . '
            WHERE sidebar_id = ?
        ', [$ID]);
    }

    /**
     * Returns sidebar by plugin ID
     *
     * @param  string $ID Plugin ID
     * 
     * @return array
     */
    public function getByPluginID( int $pluginID )
    {
        return $this->db->query('
            SELECT *
            FROM ' . TABLE_SIDEBAR . '
            WHERE plugin_id = ?
        ', [$pluginID]);
    }
}