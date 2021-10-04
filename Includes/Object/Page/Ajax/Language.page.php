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

namespace Page\Ajax;

use Model\Ajax;

/**
 * Language
 */
class Language extends \Page\Page
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
        
                $ajax->ok();

                switch ($ajax->get('process')) {

                    case '/Post/Editor':
                    case '/ProfilePost/Editor':
                    case '/ProfilePostComment/Editor':
                    case '/ConversationMessage/Editor':

                        $ajax->data([
                            'button' => $this->file('/Blocks/Visualization/Block/Buttons/Save.phtml')
                        ]);
                    break;

                    case '/Topic/Delete':
                    case '/Post/Delete':
                    case '/ProfilePost/Delete':
                    case '/ProfilePostComment/Delete':

                        $ajax->data([
                            'windowTitle' => $this->language->get('L_WINDOW')['L_TITLE']['L_CONFIRM'],
                            'windowClose' => $this->language->get('L_NO'),
                            'windowSubmit' => $this->language->get('L_YES'),
                            'windowContent' => $this->language->get('L_WINDOW')['L_DESC'][$ajax->get('process')]
                        ]);
                    break;

                    case '/Post/Report':
                    case '/Topic/Report':
                    case '/ProfilePost/Report':
                    case '/ProfilePostComment/Report':
                    
                        $ajax->data([
                            'windowTitle' => $this->language->get('L_WINDOW')['L_TITLE']['L_REPORT'],
                            'windowClose' => $this->language->get('L_BUTTON')['L_CANCEL'],
                            'windowSubmit' => $this->language->get('L_BUTTON')['L_SUBMIT'],
                            'windowContent' => $this->language->get('L_WINDOW')['L_DESC']['L_REPORT'] . '<br><textarea></textarea>'
                        ]);
                    break;

                    default:
                        $ajax->false();
                    break;
                }
            }
        );
        $ajax->end();
    }
}