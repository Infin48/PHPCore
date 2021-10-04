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

namespace Page;

/**
 * Page
 */
abstract class Page
{
    /**
     * @var object $page Page class
     */
    protected object $page;

    /**
     * @var \Model\Data $data Data
     */
    protected \Model\Data $data;

    /**
     * @var \Style\Style $style Style
     */
    protected \Style\Style $style;

    /**
     * @var \Model\Language $language Language
     */
    public \Model\Language $language;

    /**
     * @var \Process\Process $page Process
     */
    protected \Process\Process $process;

    /**
     * Initialise page
     *
     * @return void
     */
    public function ini()
    {
        if (isset($this->settings['template'])) {

            $this->style->setTemplate($this->settings['template']);
        }

        $this->data->head['title'] = $this->language->get('L_TITLE')[get_class($this)] ?? $this->data->head['title'];
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
        $message = $this->language->get('L_NOTICE')[$notice] ?? $notice;

        $this->style->data = $this->data;
        $this->style->language = $this->language;

        $this->style->notice($message);
        $this->style->show();
    }

    /**
     * Body method for every page
     *
     * @return void
     */
    abstract protected function body();
}