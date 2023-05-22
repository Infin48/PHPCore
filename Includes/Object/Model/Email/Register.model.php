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

/**
 * Register
 */
class Register extends Email
{
    /**
     * Constructor
     */
    public function __construct( \App\Model\System $system, \App\Model\Language $language )
    {
        parent::__construct( system: $system, language: $language );
        
        $this->mail->Subject = $system->get('site.name') . ' - ' . $language->get('L_EMAIL.L_ACTIVATION.L_SUBJECT');
        $this->mail->Body    = strtr($language->get('L_EMAIL.L_ACTIVATION.L_BODY'), [
            '{url}' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/verify/type-account/code-{code}/'
        ]);
    }
}

