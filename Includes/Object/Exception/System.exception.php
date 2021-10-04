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
use Model\System as _System;

/**
 * System 
 */
class System extends \Exception
{
    /**
     * Construct
     *
     * @param string $error
     * @param array $assign
     */
    public function __construct( string $error, array $assign = null )
    {
        $language = new Language();
        $system = new _System();

        if (!$language->get()) {
            
            // DEFAULT LANGUAGE
            $this->language             = new Language(
                language: 'cs'
            );
        }

        extract($language->get());

        if (isset($assign)) {

            foreach ($assign as $key => $value) {
                $assign['{' . $key . '}'] = $value;
            }

            $error = $language[$error] ? strtr($language->get($error), $assign) : $error;
        }

        if (defined('AJAX')) {
            echo json_encode([
                'error' => $error
            ]);
            exit();
        }

        require ROOT . '/Includes/Object/Exception/Template/Body.phtml';
        exit();
    }
}
