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

namespace App\Page;

/**
 * Page
 */
abstract class Page
{
    /**
     * @var \App\Model\Data $data Data
     */
    protected \App\Model\Data $data;

    /**
     * @var \App\Model\Database $db Database
     */
    protected \App\Model\Database $db;

    /**
     * @var \App\Style\Style $style Style
     */
    protected \App\Style\Style $style;

    /**
     * Initialise page
     *
     * @return void
     */
    public function __construct( \App\Model\Data $data, \App\Model\Database $db )
    {
        $this->db = $db;
        $this->data = $data;

        if (isset($this->template))
        {
            \App\Style\Style::addTemplate($this->template);
        }
        
        if (get_class($this) === 'App\Page\Router')
        {
            return;
        }

        $language = $data->get('inst.language');

        if ($_ = $language->get('L_TITLE.' . str_replace('Page\App\\', '\\', get_class($this))))
        {
            $data->set('data.head.title', $_);
        }
    }

    /**
     * Shows notice to page
     *
     * @param  string $notice The notice
     * 
     * @return void
     */
    public function notice( string $notice )
    {
        $language = $this->data->get('inst.language');

        $message = $language->get('L_NOTICE.' . $notice);
        if (!$message)
        {
            $message = $notice;
        }

        new \App\Style\Style(
            data: $this->data,
            notice: $message
        );
    }

    protected function checkFormSubmit()
    {
        // Data
        $data = $this->data;

        // File
        $post = new \App\Model\Post();

        if (!isset($data->form))
        {
            return;
        }

        if (!$post->get('submit'))
        {
            return;
        }

        foreach ($data->form->get('body') as $name => $input)
        {
            // Input is required
            if (isset($input['options']['required']))
            {
                if (!isset($_POST[$name]) or empty($_POST[$name]))
                {
                    throw new \App\Exception\Notice($name);
                }
            }

            if (!isset($_POST[$name])) 
            {
                $_POST[$name] = '';
            }

            if (!is_string($_POST[$name]))
            {
                $_POST[$name] = '';
            }

            $_POST[$name] = strip_tags($_POST[$name]);
        }

        $return = $data->form->get('options.page')->{$data->form->get('options.method')}( $this->data, $this->db, new \App\Model\Post );
    }

    /**
     * Body method for every page
     *
     * @return void
     */
    abstract public function body( \App\Model\Data $data, \App\Model\Database $db );
}