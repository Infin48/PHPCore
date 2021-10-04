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

use Model\Database\Query;

/**
 * ProcessExtend
 */
class ProcessExtend {

    /**
     * @var string $redirectURL Url where will be user redirected after process execution
     */
    public string $redirectURL = '';

    /**
     * @var bool $refresh Refresh value
     */
    public bool $refresh = false;

    /**
     * @var string $process Name of process
     */
    private string $process;

    /**
     * @var int $id ID
     */
    protected static int $id;
    
    /**
     * @var \Model\Database\Query $db Database
     */
    protected \Model\Database\Query $db;
    
    /**
     * @var \Model\Permission $perm Permission 
     */
    protected \Model\Permission $perm;

    /**
     * @var \Process\ProcessData $data ProcessData
     */
    protected \Process\ProcessData $data;

    /**
     * @var \Model\System $system System
     */
    protected \Model\System $system;

    /**
     * @var \Process\ProcessCheck $check ProcessCheck
     */
    protected \Process\ProcessCheck $check;
        
    /**
     * Constructor
     *
     * @param  string $process Name of process
     * @param  \Model\System $system System
     * @param  \Model\Permission $perm Permission
     */
    public function __construct( string $process, \Model\System $system, \Model\Permission $perm )
    {
        $this->db = new Query();
        $this->perm = $perm;
        $this->check = new ProcessCheck();
        $this->system = $system;
        $this->process = $process;
    }

    /**
     * Returns ID
     * 
     * @return int
     */
    public function getID()
    {
        return self::$id ?? $this->db->lastInsertId();
    }
    
    /**
     * Loads data to process
     *
     * @param  array $data The data
     * 
     * @return void
     */
    public function data( array $data )
    {
        $this->data = new ProcessData($data);
    }

    /**
     * Sets redirect url
     *
     * @param  string $path URL
     * 
     * @return void
     */
    protected function redirect( string $path )
    {
        $this->redirectURL = $path;
    }

    /**
     * Refresh page after process
     * 
     * @return void
     */
    protected function refresh()
    {
        $this->refresh = true;
    }

    /**
     * Adds record to log
     * 
     * @param string $text Usually some name of forum, group, user etc...
     *
     * @return void
     */
    protected function log( string $text = null )
    {
        $this->db->insert(TABLE_LOG, [
            'user_id'       => LOGGED_USER_ID,
            'log_text'      => $text ?? '',
            'log_action'    => $this->process
        ]);
    }

    /**
     * Sends notification to user
     * 
     * @param int $id Id of item
     * @param int $to User id
     * @param bool $replace If true and user will have unreaded same notification, time will be updated otherwise will be added another notification to user
     *
     * @return void
     */
    protected function notifi( int $id, int $to, bool $replace = false )
    {
        if (LOGGED_USER_ID == $to) {
            return;
        }

        $_id = '';
        if ($replace === true) {
            $_id = $this->db->query('
                SELECT user_notification_id
                FROM ' . TABLE_USERS_NOTIFICATIONS . '
                WHERE user_notification_type = ? AND user_notification_type_id = ? AND user_id = ?
            ', [$this->process, $id, LOGGED_USER_ID]);
        }

        if (isset($_id['user_notification_id'])) {
            $this->db->query('UPDATE ' . TABLE_USERS_NOTIFICATIONS . ' SET user_notification_created = NOW() WHERE user_notification_id = ?', [$_id['user_notification_id']]);
        } else {
            $this->db->insert(TABLE_USERS_NOTIFICATIONS, [
                'user_id'                       => LOGGED_USER_ID,
                'to_user_id'                    => (int)$to,
                'user_notification_type'        => $this->process,
                'user_notification_type_id'     => (int)$_id ?: $id
            ]);
        }
    }
    
    /**
     * Updates system session
     *
     * @return void
     */
    protected function updateSession()
    {
        $this->db->table(TABLE_SETTINGS, [
            'session' => RAND
        ]);
    }

    /**
     * Executes process
     *
     * @return void
     */
    protected function require( string $process, array $data = [] )
    {
        $process = 'Process\\' . implode('\\', explode('/', $process));
        $process = new $process($process, $this->system, $this->perm);
        $process->data($data);
        $process->process();

        $this->refresh = $process->refresh;
        $this->redirectURL = $process->redirectURL;
    }
}