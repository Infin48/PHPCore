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

namespace Model\Account;

use Model\Cookie;
use Model\Session;
use Model\Database\Query;
use Model\Mail\MailRegister;

use Block\User;

/**
 * Login
 */
class Login
{
    /**
     * @var string $username Name of user
     */
    private string $username;

    /**
     * @var string $password Password of user
     */
    private string $password;

    /**
     * @var bool $remember If true - login will be remembered
     */
    private bool $remember = false;

    /**
     * @var array $result Database data
     */
    private array $result;

    /**
     * @var \Block\User $user User
     */
    private \Block\User $user;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $pasword
     * @param int $remember
     */
    public function __construct( string $username, string $password, int $remember )
    {        
        $this->username = $username;
        $this->password = $password;
        $this->remember = (bool)$remember;

        $this->db = new Query();
        $this->user = new User();
    }

    /**
     * Checks if login is valid
     * 
     * @throws \Exception\Notice If is found any error in login data
     *
     * @return bool
     */
    private function validate()
    {
        $this->result = $this->user->getByName($this->username) ?: [];

        if (!$this->result or password_verify($this->password, $this->result['user_password']) === false) {
            throw new \Exception\Notice('login_validate');
        }

        if ($this->result['account_code']) {

            // SEND AN EMAIL TO VERIFY ACCOUNT
            $mail = new MailRegister();
            $mail->mail->addAddress($this->result['user_email'], $this->result['user_name']);
            $mail->assign(['code' => $this->result['account_code']]);
            $mail->send();

            throw new \Exception\Notice('account_not_activated_send');
        }

        return true;
    }

    /**
     * Logs user
     *
     * @return void
     */
    public function login()
    {
        if ($this->validate() !== true) {
            return false;
        }

        $this->db->query('UPDATE ' . TABLE_USERS . ' SET user_hash = ?, user_last_activity = NOW() WHERE user_id = ?', [$token = md5(uniqid(mt_rand(), true)), $this->result['user_id']]);

        if ($this->remember === true) {

            Cookie::put('token', $token, 365 * 24 * 3600);
            Session::delete('token');

        } else {

            Session::put('token', $token);
            Cookie::delete('token');
        }
    }
}
