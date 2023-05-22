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

namespace App\Model\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Email
 */
class Email
{
    /**
     * @var \PHPMailer\PHPMailer\PHPMailer $mail PHPMailer
     */
    public \PHPMailer\PHPMailer\PHPMailer $mail;
      
    /**
     * Constructor
     */
    public function __construct( \App\Model\System $system, \App\Model\Language $language )
    {
        require ROOT . '/Assets/PHPMailer/Exception.php';
        require ROOT . '/Assets/PHPMailer/PHPMailer.php';
        require ROOT . '/Assets/PHPMailer/SMTP.php';

        $this->mail = new PHPMailer(true);

        try {
            //Server settings
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->mail->Debugoutput = 'error_log';
            $this->mail->CharSet = 'UTF-8';

            if ($system->get('email.smtp_enabled'))
            {
                $this->mail->isSMTP();
                $this->mail->Host       = $system->get('email.smtp_host');
                $this->mail->SMTPAuth   = true;
                $this->mail->Username   = $system->get('email.smtp_username');
                $this->mail->Password   = $system->get('email.smtp_password');
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $this->mail->Port       = (int)$system->get('email.smtp_port');
            }

            $this->mail->setFrom($system->get('email.prefix') . '@' . $_SERVER['SERVER_NAME'], $system->get('site.name'));

        } catch (Exception $e) {
            throw new \App\Exception\Notice($this->mail->ErrorInfo);
        }
    }

    /**
     * Assigns data to e-mail body
     *
     * @return void
     */
    public function assign( array $data )
    {
        $_data = [];
        foreach ($data as $key => $value) {
            $_data['{' . $key . '}'] = $value;
        }

        $this->mail->Body = strtr($this->mail->Body, $_data);
    }

    /**
     * Sends e-mail
     *
     * @return void
     */
    public function send()
    {
        $this->mail->isHTML(true);
        try {
            $this->mail->send();
        } catch (Exception $e) {
            throw new \App\Exception\Notice($this->mail->ErrorInfo, []);
        }
    }
}

