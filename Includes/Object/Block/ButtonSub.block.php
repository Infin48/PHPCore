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

namespace Block;

/**
 * ButtonSub
 */
class ButtonSub extends Block
{
    /**
     * Returns sub button
     *
     * @param  int $buttonSubID Button sub ID
     * 
     * @return array
     */
    public function get( int $buttonSubID )
    {
        return $this->db->query('SELECT * FROM ' . TABLE_BUTTONS_SUB . ' WHERE button_sub_id = ?', [$buttonSubID]);
    }

    /**
     * Returns all sub buttons from dropdown
     *
     * @param  int $dropdownID Dropdown ID
     * 
     * @return array
     */
    public function getParent( int $dropdownID )
    {
        return $this->db->query('
            SELECT *, IFNULL(page_url, button_sub_link) AS button_sub_link
            FROM ' . TABLE_BUTTONS_SUB . '
            LEFT JOIN ' . TABLE_PAGES . ' ON pg.page_id = bs.page_id
            WHERE button_id = ?
            ORDER BY position_index DESC
        ', [$dropdownID], ROWS);
    }
}