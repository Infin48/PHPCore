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
 * User
 */
class User extends Block
{
    /**
     * Returns user by user ID
     *
     * @param  int $userID User ID
     * 
     * @return array
     */
    public function get( int $userID )
    {
        return $this->db->query('
            SELECT u.*, g.group_name, g.group_class_name, g.group_index, fp.forgot_code, va.account_code, ve.email_code
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_FORGOT . ' ON fp.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_ACCOUNT . ' ON va.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_EMAIL . ' ON ve.user_id = u.user_id
            WHERE u.user_id = ? AND user_deleted = 0
        ', [$userID]);
    }

    /**
     * Returns user by user name
     *
     * @param  string $name User name
     * 
     * @return array
     */
    public function getByName( string $userName )
    {
        return $this->db->query('
            SELECT u.*, g.group_name, g.group_class_name, g.group_index, fp.forgot_code, va.account_code
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_FORGOT . ' ON fp.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_ACCOUNT . ' ON va.user_id = u.user_id
            WHERE u.user_name = ?
        ', [$userName]);
    }

    /**
     * Returns user by user e-mail
     *
     * @param  string $userEmail User e-mail
     * 
     * @return array
     */
    public function getByEmail( string $userEmail )
    {
        return $this->db->query('
            SELECT u.*, g.group_name, g.group_class_name, g.group_index, fp.forgot_code, va.account_code
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_FORGOT . ' ON fp.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_ACCOUNT . ' ON va.user_id = u.user_id
            WHERE u.user_email = ?
        ', [$userEmail]);
    }

    /**
     * Returns user by user hash
     *
     * @param  string $userHash User hash
     * 
     * @return array
     */
    public function getByHash( string $userHash )
    {
        return $this->db->query('
            SELECT u.*, g.group_name, g.group_class_name, g.group_index, g.group_permission, ve.email_code
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_VERIFY_EMAIL . ' ON ve.user_id = u.user_id
            WHERE user_hash = ?
        ', [$userHash]) ?: [];
    }

    /**
     * Returns users unreaded conversations
     *
     * @param  int $userID User ID
     * 
     * @return array
     */
    public function getUnread( int $userID )
    {
        return array_column($this->db->query('
            SELECT conversation_id
            FROM ' . TABLE_USERS_UNREAD . '
            WHERE user_id = ?
        ', [$userID], ROWS), 'conversation_id');
    }
    
    /**
     * Returns all users
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('
            SELECT ' . $this->select->user() . ', user_admin, group_name, group_index, user_reputation, user_registered
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            WHERE user_deleted = 0
            ORDER BY user_admin DESC, group_index DESC, user_registered ASC
            LIMIT ?, ?
        ', [$this->pagination['offset'], $this->pagination['max']], ROWS);
    }

    /**
     * Returns count of users
     * 
     * @return int
     */
    public function getAllCount()
    {
        return (int)$this->db->query('SELECT COUNT(*) as count FROM ' . TABLE_USERS . ' WHERE user_deleted = 0')['count'];
    }

    /**
     * Returns online users
     *
     * @return array
     */
    public function getOnline()
    {
        return $this->db->query('
            SELECT ' . $this->select->user() . '
            FROM ' . TABLE_USERS. '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id 
            WHERE user_last_activity > DATE_SUB(NOW(), INTERVAL 1 MINUTE) AND user_deleted = 0
        ', [], ROWS);
    }

    /**
     * Returns count of recent registered users
     *
     * @return int
     */
    public function getRecentCount()
    {
        return $this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_USERS . '
            WHERE user_registered > DATE_SUB(CURDATE(), INTERVAL 1 HOUR) AND user_deleted = 0
        ')['count'];
    }

    /**
     * Returns last registered users
     *
     * @param int $number Number of users
     * 
     * @return array
     */
    public function getRecent( int $number = 5 )
    {
        return $this->db->query('
            SELECT ' . $this->select->user() . ', u.user_admin, u.user_registered, g.group_name, g.group_index
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            WHERE user_deleted = 0
            ORDER BY user_registered DESC
            LIMIT ?
        ', [$number], ROWS);
    }

    /**
     * Returns user by forgot code
     *
     * @param string $forgotCode Forgot code
     * 
     * @return array
     */
    public function getByForgotCode( string $forgotCode )
    {
        return $this->db->query('
            SELECT user_id
            FROM ' . TABLE_FORGOT . '
            WHERE forgot_code = ?
        ', [$forgotCode]);
    }

    /**
     * Returns user by account code
     *
     * @param string $accountCode Account code 
     * 
     * @return array
     */
    public function getByAccountCode( string $accountCode )
    {
        return $this->db->query('
            SELECT user_id
            FROM ' . TABLE_VERIFY_ACCOUNT . '
            WHERE account_code = ?
        ', [$accountCode]);
    }

    /**
     * Returns user by email code
     *
     * @param string $emailCode Email code 
     * 
     * @return array
     */
    public function getByEmailCode( string $emailCode )
    {
        return $this->db->query('
            SELECT user_id, user_email
            FROM ' . TABLE_VERIFY_EMAIL . '
            WHERE email_code = ?
        ', [$emailCode]);
    }

    /**
     * Returns ID of all registered users
     *
     * @return array
     */
    public function getAllID()
    {
        return array_column($this->db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_deleted = 0', [], ROWS), 'user_id');
    }
}