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
 * Process
 */
class Process extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'loggedIn' => true,
        'permission' => 'admin.?'
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

            require: ['id', 'process'],

            exec: function ( \Model\Ajax $ajax ) {
                
                $ajax->process(

                    process: $this->process,
                    permission: $this->user->perm,
        
                    key: $ajax->get('id'),
                    type: $ajax->get('process'),
        
                    success: function ( \Model\Ajax $ajax ) {                        
                        $ajax->ok();
                    }
                );
            }
        );
        $ajax->end();
    }
}