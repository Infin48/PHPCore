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

use Block\User;

use Model\Database\Query;

/**
 * Register
 */
class Register extends \Model\Model
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
     * @var string $email Email of user
     */
    private string $email;

    /**
     * @var string $token Token of recaptcha
     */
    private string $token;

    /**
     * @var string $code Generated code for new user to activate account
     */
    private string $code;

    /**
     * @var string $url URL of recaptcha
     */
    private string $url = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var \Block\User $user User block
     */
    private \Block\User $user;

    /**
     * @var \Model\Database\Query $db Query
     */
    private \Model\Database\Query $db;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $token
     */
    public function __construct( string $username, string $password, string $email, string $token )
    {
        parent::__construct();

        $this->db = new Query();
        $this->user = new User();

        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->token = $token;
    }

    /**
     * Checks if regitration is valid
     * 
     * @throws \Exception\Notice If is found any error in registration data
     *
     * @return bool
     */
    private function validate()
    {
        $options = [
            'http' => [
                'header'  => 'Content-type: application/x-www-form-urlencoded\r\n',
                'method'  => 'POST',
                'content' => http_build_query([
                    'secret' => $this->system->settings->get('registration.key_secret'),
                    'response' => $this->token,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ])
            ]
        ];

        $res = json_decode(file_get_contents($this->url, false, stream_context_create($options)), true);

        if ($res['success'] != true) {
            throw new \Exception\Notice('recaptcha');
        }

        if (!empty($this->user->getByName($this->username))) {
            throw new \Exception\Notice('user_name_exist');
        }

        if (!empty($this->user->getByEmail($this->email))) { 
            throw new \Exception\Notice('user_email_exist');
        }

        return true;
    }

    /**
     * Registers user
     *
     * @return bool
     */
    public function register()
    {
        if ($this->validate() !== true) {
            return false;
        }

        $this->db->insert(TABLE_USERS, [
            'group_id' => $this->system->settings->get('default_group'),
            'user_name' => $this->username,
            'user_email' => $this->email,
            'user_password' => password_hash($this->password, PASSWORD_DEFAULT),
            'user_profile_image' => getProfileImageColor()
        ]);
        
        $this->code = md5(mt_rand());

        $this->db->insert(TABLE_VERIFY_ACCOUNT, [
            'account_code' => $this->code,
            'user_id' => $this->db->lastInsertId()
        ]);

        return true;
    }

    /**
     * Returns generated code to activate account
     *
     * @return string The code
     */
    public function getCode()
    {
        return $this->code;
    }
}