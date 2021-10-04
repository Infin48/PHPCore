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

namespace Visualization\Admin\Lists;

use Model\Url;

/**
 * Lists
 */
class Lists extends \Visualization\Visualization
{
    /**
     * @var array $button Pre-defined buttons
     */
    private array $button = [
        'add' => [
            'href' => '/add/'
        ],
        'info' => [
            'icon' => 'fas fa-info',
            'href' => '/show/{id}',
            'title' => 'L_DETAILS'
        ],
        'up' => [
            'ajax' => 'up',
            'icon' => 'fas fa-caret-up'
        ],
        'down' => [
            'ajax' => 'down',
            'icon' => 'fas fa-caret-down'
        ],
        'edit' => [
            'href' => '/show/{id}/',
            'icon' => 'fas fa-pencil-alt',
            'title' => 'L_EDIT'
        ],
        'delete' => [
            'ajax' => 'process-window',
            'icon' => 'fas fa-trash',
            'title' => 'L_DELETE'
        ],
        'setup' => [
            'href' => '/setup/{id}/',
            'icon' => 'fas fa-cog'
        ]
    ];

    /**
     * Adds label to current object
     *
     * @param  string $color Label color
     * @param  string $icon Label icon
     * @param  string $text Label text
     * 
     * @return void
     */
    public function addLabel( string $color, string $icon = null, string $text = null )
    {
        $this->obj->set->data('label', array_merge($this->obj->get->data('label') ?: [], [['color' => $color, 'text' => $text ?? '', 'icon' => $icon ?? '']]));
    }

    /**
     * Executes code for every object
     *
     * @param  \Visualization\Visualization $visual
     * 
     * @return void|false
     */
    protected function each_clb( \Visualization\Visualization $visual )
    {
        foreach ((array)$visual->obj->get->button() as $btnName => $btn) {

            $btn['data'] ??= [];

            // PREPEND PATH TO TEMPLTE IF IS SET
            if (isset($btn['options']['template'])) {
                $btn['options']['template'] = ROOT . '/Includes/Admin/Styles/Default/Templates/Blocks/Visualization/Lists' . $btn['options']['template'];
            }

            // MERGE BUTTON DATA WITH PREDEFINED IF IS SET
            if (isset($this->button[$btnName])) {
                $btn['data'] = array_merge($this->button[$btnName], $btn['data']);
            }

            // IF BUTTON HAS HREF PARAMETER
            if (isset($btn['data']['href'])) {

                // ASSIGN VARIABLES TO URL
                foreach ($visual->obj->get->data() as $key => $value) {
                    if (!is_array($value)) {
                        $btn['data']['href'] = strtr($btn['data']['href'], ['{' . $key . '}' => $value]);
                    }
                }

                switch (substr($btn['data']['href'], 0, 1)) {
            
                    case '$':
                        $btn['data']['href'] = substr($btn['data']['href'], 1);
                    break;

                    case '~':
                        $btn['data']['href'] = Url::build(substr($btn['data']['href'], 1));
                    break;
    
                    default:
                        $btn['data']['href'] = Url::build(Url::getURL() . $btn['data']['href']);
                    break;
                }
            }

            // SET EDITED DATA TO BUTTON
            $visual->obj->set->button($btnName, $btn);
        }
    }
}