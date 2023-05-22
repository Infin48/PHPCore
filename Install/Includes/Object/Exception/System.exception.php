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

namespace App\Exception;

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
        $language = new \App\Model\Language();

        if ($_SERVER['REQUEST_URI'] == '/update/install/')
        {
            echo json_encode([
                'status' => 'error',
                'error' => $error,
                'title' => $language->get('L_UPDATE.L_ERROR'),
                'button' => $language->get('L_RETRY')
            ]);

            exit();
        }

        require ROOT . '/Includes/Object/Exception/Template/Body.phtml';

        exit();
    }
}