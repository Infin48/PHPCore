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

namespace Page\Admin\Ajax;

use Model\Ajax;

/**
 * Execute
 */
class Execute extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'loggedIn' => true
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $ajax = new Ajax();

        $ajax->ajax(

            require: ['process'],

            exec: function ( \Model\Ajax $ajax ) {
               
                $ajax->process(
            
                    process: $this->process,
        
                    type: $ajax->get('process'),
                    method: 'call',
        
                    success: function ( \Model\Ajax $ajax ) {
                        $ajax->ok();
                    }
                );
            }
        );
        $ajax->end();
    }
}