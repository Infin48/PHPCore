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

namespace Process;

/**
 * ProcessHTMLConfiguration
 */
class ProcessHTMLConfiguration {

    /**
     * @var array $HTMLPurifierConfig HTML Purifier config
     */
    private \HTMLPurifier_Config $config;
    
    /**
     * Constructor
     * 
     * @param string $configuration Configuration name
     */
    public function __construct( string $configuration )
    {
        require ROOT . '/Assets/HTMLPurifier/HTMLPurifier.auto.php';
        $this->config = \HTMLPurifier_Config::createDefault();

        $this->{$configuration}();
    }
    
    /**
     * Returns HTML Purifier
     *
     * @return \HTMLPurifier
     */
    public function get()
    {
        return new \HTMLPurifier($this->config);
    }
    
    /**
     * Default configuration
     *
     * @return void
     */
    private function default()
    {
        $this->config->set('CSS.Proprietary', true);
        $this->config->set('CSS.Trusted', true);

        $this->config->set('HTML.Allowed', 'em,strong,del,pre,code,ul,li,ol,a[href],img[src],span[data-user],p[style],blockquote,pre,code,br,h1,h2,h3,h4');
        $this->config->set('CSS.AllowedProperties', 'text-align');

        $def = $this->config->getHTMLDefinition(true);
        $def->addAttribute('span', 'data-user', 'Text');
    }

    /**
     * Comment configuration
     *
     * @return void
     */
    private function small()
    {
        $this->config->set('HTML.Allowed', 'em,strong,del,a[href],p,br');
    }

    /**
     * Comment configuration
     *
     * @return void
     */
    private function medium()
    {
        $this->config->set('HTML.Allowed', 'em,strong,del,a[href],p,ul,li,ol,br');
    }
}