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
 * Label
 */
class Label extends Table
{
    /**
     * Returns label
     * 
     * @param int $ID Label ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('SELECT label_id, label_name, label_class, label_color FROM ' . TABLE_LABELS . ' WHERE label_id = ?', [$ID]);
    }

    /**
     * Returns all labels
     * 
     * @return array
     */
    public function all()
    {
        return $this->db->query('SELECT * FROM ' . TABLE_LABELS . ' ORDER BY position_index DESC', [], ROWS);
    }

    /**
     * Returns ID of all labels
     * 
     * @return array
     */
    public function getAllID()
    {
        return array_column($this->db->query('SELECT label_id FROM ' . TABLE_LABELS, [], ROWS), 'label_id');
    }
}