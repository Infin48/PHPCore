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


/**
 * Style
 */
class Style
{
    /**
     * @var array $templates List of templates
     */
    private array $templates = ['shift'];

    /**
     * @var \Model\Form $form Form
     */
    public \Model\Form $form;

    /**
     * @var \Model\Data $data Data
     */
    public \Model\Data $data;

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
     * Sets notice
     *
     * @param  string $notice
     * 
     * @return void
     */
    public function notice( string $notice )
    {
        $this->data->data([
            'notice' => $notice
        ]);
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
        
        require ROOT . '/Install/Style/Templates' . $this->templates[0] . '.phtml';
    }
}