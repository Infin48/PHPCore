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

namespace Model\Mail;

/**
 * MailTest
 */
class MailTest extends Mail
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->mail->Subject = $this->system->settings->get('site.name') . ' - ' . $this->language->get('L_MAIL_TEST_SUBJECT');
        $this->mail->Body    = $this->language->get('L_MAIL_TEST_BODY');
    }
}