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

namespace Exception;

use Model\Language;

/**
 * System 
 */
class System extends \Exception
{
    /**
     * Constructor
     *
     * @param string $error The error
     */
    public function __construct( string $error )
    {
        $language = new Language();

        if (AJAX === true) {

            echo json_encode([
                'status' => 'error',
                'error' => $error,
                'title' => $language->get('L_INSTALL_ERROR'),
                'button' => $language->get('L_RETRY')
            ]);

            exit();
        }

        extract($language->get());

        require ROOT . '/Includes/Object/Exception/Template/Body.phtml';

        exit();
    }
}