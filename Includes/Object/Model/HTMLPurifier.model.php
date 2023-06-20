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

namespace App\Model;

/**
 * HTMLPurifier
 */
class HTMLPurifier {

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
     * @return string
     */
    public function purify( string $html )
    {
        return (new \HTMLPurifier($this->config))->purify($html);
    }
    
    /**
     * Bif configuration - default
     *
     * @return void
     */
    private function big()
    {
        $this->config->set('HTML.Allowed', 'u,table[border|cellspacing|cellpadding],tbody,tr[class],th[rowspan|valign|colspan],td[rowspan|valign|colspan],thead,ul,li,ol,h1,h2,h3,h4,blockquote,code,pre,em[style],strong[style],del[style],a[contenteditable|href|class|mention|target|title],p[style],br,span[style|data-user],iframe[src|allowfullscreen],video,img[src|alt|width|height]');
        $this->config->set('HTML.SafeIframe', true);
        $this->config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');

        $this->config->set('CSS.AllowedProperties', 'background-color,color,text-align');

        $def = $this->config->getHTMLDefinition(true);
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $def->addElement(   // add video tag
            'video',   // name
            'Block',  // content set
            'Flow', // allowed children
            'Common', // attribute collection
            [ // attributes
                'src' => 'URI',
                'width' => 'Length',
                'height' => 'Length',
                'style' => 'CDATA',
                'controls' => 'CDATA'
            ]
        );
        $def->addElement( 
            'source',   
            'Block', 
            'Flow',
            'Common',
            [ // attributes
                'src' => 'URI',
                'type' => 'CDATA'
            ]
        );
        $def->addAttribute('table', 'border', 'Number');
        $def->addAttribute('table', 'cellspacing', 'Number');
        $def->addAttribute('table', 'cellpadding', 'Number');
        $def->addAttribute('tr', 'class', new \HTMLPurifier_AttrDef_Enum(['tr-category']));
        $def->addAttribute('th', 'rowspan', 'Number');
        $def->addAttribute('th', 'colspan', 'Number');
        $def->addAttribute('th', 'valign', new \HTMLPurifier_AttrDef_Enum(['top', 'center', 'bottom', 'baseline']));
        $def->addAttribute('td', 'rowspan', 'Number');
        $def->addAttribute('td', 'colspan', 'Number');
        $def->addAttribute('td', 'valign', new \HTMLPurifier_AttrDef_Enum(['top', 'center', 'bottom', 'baseline']));
        $def->addAttribute('span', 'data-user', 'Text');
        $def->addAttribute('a', 'mention', 'Text');
        $def->addAttribute('a', 'title', 'Text');
        $def->addAttribute('a', 'target', new \HTMLPurifier_AttrDef_Enum(['_self', '_parent', '_top', '_blank']));
        $def->addAttribute('a', 'contenteditable', new \HTMLPurifier_AttrDef_Enum(['true', 'false']));
    }

    /**
     * Small configuration
     *
     * @return void
     */
    private function small()
    {
        $this->config->set('HTML.Allowed', 'u,em,strong,del,a[contenteditable|href|class|mention|target|title]');
        $def = $this->config->getHTMLDefinition(true);
        $def->addAttribute('a', 'mention', 'Text');
        $def->addAttribute('a', 'title', 'Text');
        $def->addAttribute('a', 'target', new \HTMLPurifier_AttrDef_Enum(['_self', '_parent', '_top', '_blank']));
        $def->addAttribute('a', 'contenteditable', new \HTMLPurifier_AttrDef_Enum(['true', 'false']));
    }

    /**
     * Medium configuration
     *
     * @return void
     */
    private function notification()
    {
        $this->config->set('HTML.Allowed', 'u,em,strong,del,a[href|target|title],br');
        $def = $this->config->getHTMLDefinition(true);
        $def->addAttribute('a', 'title', 'Text');
        $def->addAttribute('a', 'target', new \HTMLPurifier_AttrDef_Enum(['_self', '_parent', '_top', '_blank']));
    }
}