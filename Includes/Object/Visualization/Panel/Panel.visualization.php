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

namespace Visualization\Panel;

/**
 * Panel
 */
class Panel extends \Visualization\Visualization
{
    /**
     * Executes code for every object
     *
     * @param  \Visualization\Visualization $visual
     * 
     * @return void
     */
    protected function each_clb( \Visualization\Visualization $visual )
    {
        if ($visual->obj->is->data('href')) {

            $href = $visual->obj->get->data('href');

            // ASSIGN VARIABLES TO URL
            foreach ($visual->obj->get->data() as $key => $value) {
                if (!is_array($value)) {
                    $href = str_replace('{' . $key . '}', $value, $href);
                }
            }
            
            switch (substr($href, 0, 1)) {
                
                case '$':
                break;
                    
                case '~':
                    $href = $this->system->url->build(substr($href, 1));
                break;
                    
                default:
                    $href = $this->system->url->build(URL . $href);
                break;
            }
            $visual->obj->set->data('href', $href);
        }
    }
}
