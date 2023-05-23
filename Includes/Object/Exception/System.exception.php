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
     * Construct
     *
     * @param string $error
     * @param array $assign
     */
    public function __construct( string $error, array $assign = null )
    {
        if (isset($assign))
        {
            foreach ($assign as $key => $value)
            {
                $assign['{' . $key . '}'] = $value;
            }

            $error = $language[$error] ? strtr($language->get($error), $assign) : $error;
        }

        $error .= '<br>Error on line ' . $this->getLine() . ' in '.$this->getFile() . '<br>' . $this->getTraceAsString();

        if (defined('AJAX'))
        {
            echo json_encode([
                'status' => 'error',
                'message' => $error
            ]);
            exit();
        }

        $preview = '';

        // If in session is saved template to preview
        if (\App\Model\Session::exists('preview'))
        {
            // If this template really exists
            if (file_exists(ROOT . '/Styles/' . \App\Model\Session::get('preview')))
            {
                $preview = \App\Model\Session::get('preview');
            }
        }

        require ROOT . '/Includes/Object/Exception/Template/Body.phtml';
        exit();
    }
}
