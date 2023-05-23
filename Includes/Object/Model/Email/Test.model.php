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
 * Test
 */
class Test extends Email
{
    /**
     * Constructor
     */
    public function __construct( \App\Model\System $system, \App\Model\Language $language )
    {
        parent::__construct( system: $system, language: $language );

        $this->mail->Subject = $system->get('site.name') . ' - ' . $language->get('L_EMAIL.L_TEST.L_SUBJECT');
        $this->mail->Body    = $language->get('L_EMAIL.L_TEST.L_BODY');
    }
}