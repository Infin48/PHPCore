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

namespace Process\Admin\Settings;

use Model\Mail\MailTest;

/**
 * EmailSend
 */
class EmailSend extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [];

    /**
     * @var array $options Process options
     */
    public array $options = [];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $email = $this->db->query('SELECT user_email FROM ' . TABLE_USERS . ' WHERE user_admin = 1');

        $mail = new MailTest();
        $mail->mail->addAddress($email['user_email']);
        $mail->send();
    }
}