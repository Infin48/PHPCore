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

use Plugin\Plugin;

class Style
{
    /**
     * @var array $templates List of templates
     */
    private array $templates = ['shift'];

    /**
     * @var array $ID List of IDs
     */
    public array $ID = [];

    /**
     * @var string $URL Page URL
     */
    public string $URL = '/';

    /**
     * @var string $URLcurrent Current page URL
     */
    public string $URLcurrent = '/';

    /**
     * @var \Model\Url $url Url
     */
    public \Model\Url $url;

    /**
     * @var \Model\Form $form Form
     */
    public \Model\Form $form;

    /**
     * @var \Model\Data $data Data
     */
    public \Model\Data $data;

    /**
     * @var \Model\Build\Build $build Builder
     */
    public \Model\Build\Build $build;

    /**
     * @var \Model\System $system System
     */
    public \Model\System $system;

    /**
     * @var \Model\Template $template Template
     */
    public \Model\Template $template;

    /**
     * @var \Model\Language $language Language
     */
    public \Model\Language $language;
    
    /**
     * Constructor
     *
     * @param  \Model\System $system
     */
    public function __construct()
    {
        $this->form = new Form();
    }

        
    /**
     * Initialise page
     * 
     * @return void
     */
    public function ini()
    {
        $this->data->plugin = Plugin::getPlugins();

        if (isset($this->template->get('body')[$this->templates[2] ?? ''])) {
            $this->templates[1] = $this->template->get('body')[$this->templates[2]];
        }

        $this->data->head['title'] = str_replace('"', '&quot;', $this->data->head['title']);

        // TRUNCATE DESCRIPTION
        $this->data->head['description'] = str_replace('"', '&quot;', truncate(strip_tags($this->data->head['description']), 250));

        // SUCCESS MESSAGE
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
        // PAGE TITLE
        $this->data->head['title'] = $this->language->get('L_ERROR');

        extract($this->language->get());

        require $this->template->template('/Error.phtml');
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
        
        if (str_starts_with($this->templates[0], '~')) {
            require ROOT . substr($this->templates[0], 1) . '.phtml';
        } else {
            require $this->template->template($this->templates[0] . '.phtml');
        }
    }
}