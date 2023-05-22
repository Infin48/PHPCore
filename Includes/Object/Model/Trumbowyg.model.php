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
 * Trumbowyg
 */
class Trumbowyg
{
    /**
     * @var string $language Trumbowyg language
     */
    private string $language;
    
    /**
     * Constructor
     * 
     * @param string $language Trumbowyh language
     */
    public function __construct( string $language )
    {
        $this->language = $language;
    }
    
    /**
     * Returns big configuration of trumbowyg
     *
     * @return array
     */
    public function big()
    {
        return [
            'lang' => $this->language,
            'autogrow' => true,
            'btns' => [
                ['viewHTML'],
                ['undo', 'redo'],
                ['formatting'],
                ['strong', 'em', 'del'],
                ['foreColor', 'backColor'],
                ['link'],
                ['insertImage', 'upload', 'noembed'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['emoji'],
                ['preformatted'],
                ['removeformat']
            ],
            'plugins' => [
                'upload' => [
                    'serverPath' => '/upload/',
                    'fileFieldName' => 'file',
                    'imageWidthModalEdit' => true
                ]
            ]
        ];
    }
     
    /**
     * Returns small configuration of trumbowyg
     *
     * @return array
     */
    public function small()
    {
        return [
            'lang' => $this->language,
            'autogrow' => true,
            'btns'=> [
                ['undo', 'redo'],
                ['strong', 'em', 'del'],
                ['link'],
                ['emoji'],
                ['removeformat']
            ]
        ];
    }
}