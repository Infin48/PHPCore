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

namespace Style;

use Model\Form;
use Model\Session;
use Model\Language;
use Model\Template;
use Model\Build\Build;

class Style
{
    /**
     * @var array $templates List of templates
     */
    private array $templates = ['shift'];

    /**
     * @var string|int $ID URL ID
     */
    public string|int $ID = 0;

    /**
     * @var string $URL Page URL
     */
    public string $URL = '/';

    /**
     * @var \Model\Form $form Form
     */
    private \Model\Form $form;

    /**
     * @var \Model\Data $data Data
     */
    private \Model\Data $data;

    /**
     * @var \Model\Build\Build $build Builder
     */
    private \Model\Build\Build $build;

    /**
     * @var \Model\System\System $system System
     */
    private \Model\System\System $system;

    /**
     * @var \Model\Template $template Template
     */
    private \Model\Template $template;

    /**
     * @var \Model\Language $language Language
     */
    private \Model\Language $language;
    
    /**
     * Constructor
     *
     * @param  \Model\System\System $system
     */
    public function __construct( \Model\System\System $system )
    {
        $this->form = new Form();
        $this->system = $system;
        $this->build = new Build();
        $this->language = new Language();
        $this->template = new Template();   

        $this->form = new Form();
    }

    /**
     * Constructor
     *
     * @param  \Model\Data $data
     * @param  \Model\Build\Build $build
     * @param  \Model\User $user
     */
    public function load( \Model\Data $data, \Model\Build\Build $build, \Model\User $user )
    {
        $this->data = $data;
        $this->user = $user;
        $this->build = $build;

        if (isset($this->system->template->get('body')[$this->templates[2] ?? ''])) {
            $this->templates[1] = $this->system->template->get('body')[$this->templates[2]];
        }

        // TRUNCATE DESCRIPTION
        $this->data->head['title'] = str_replace('"', '&quot;', $this->data->head['title']);
        $this->data->head['description'] = str_replace('"', '&quot;', truncate(strip_tags($this->data->head['description']), 250));

        // IF IS SET SUCCESS MESSAGE
        if (Session::exists('success')) {
            if (isset($this->language->get('L_NOTICE')['L_SUCCESS'][Session::get('success')])) {

                $this->data->data([
                    'message' => [
                        'text' => $this->language->get('L_NOTICE')['L_SUCCESS'][Session::get('success')],
                        'type' => 'success'
                    ]
                ]);

                Session::delete('success');
            }
        }
    }

    /**
     * Sets notice
     *
     * @param  string $notice
     * 
     * @return void
     */
    public function notice( string $notice )
    {
        $this->data->data([
            'message' => [
                'text' => $notice,
                'type' => 'warning'
            ]
        ]);
    }

    /**
     * Shows error page
     * 
     * @return void
     */
    public function error()
    {
        // DEFINE ERROR PAGE
        define('ERROR_PAGE', true);

        // LOAD WEBSITE LANGUAGE
        $this->language->load('/Languages/' . $this->system->settings->get('site.language'));

        // PAGE TITLE
        $this->data->head['title'] = $this->language->get('L_ERROR');

        extract($this->language->get());

        require $this->template->template('Error.phtml');
        exit();
    }
    
    /**
     * Sets next template
     *
     * @param  string $template Template name
     * 
     * @return void
     */
    public function setTemplate( string $template )
    {
        array_push($this->templates, $template);
    }
    
    /**
     * Shows template
     *
     * @return void
     */
    public function show()
    {
        array_shift($this->templates);
        extract($this->language->get());
        require $this->template->template($this->templates[0] . '.phtml');
    }

    /**
     * Returns current URL
     *
     * @return string
     */
    private function getURL()
    {
        return $this->URL;
    }

    /**
     * Returns ID
     *
     * @return string|int
     */
    private function getID()
    {
        return $this->ID;
    }
}