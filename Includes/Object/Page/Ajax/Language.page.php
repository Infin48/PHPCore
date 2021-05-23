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

use Model\Get;

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
        $get = new Get();

        $get->get('process') or exit();

        switch ($get->get('process')) {

            case 'Post/Editor':
            case 'ProfilePost/Editor':
            case 'ProfilePostComment/Editor':
            case 'ConversationMessage/Editor':
                $this->data->data([
                    'button' => $this->file('/Blocks/Block/Buttons/Save.phtml'),
                    'status' => 'ok'
                ]);
            break;

            case 'Post/Delete':
            case 'ProfilePost/Delete':
            case 'ProfilePostComment/Delete':
                $this->data->data([
                    'windowTitle' => $this->language->get('L_WINDOW_CONFIRM'),
                    'windowClose' => $this->language->get('L_NO'),
                    'windowSubmit' => $this->language->get('L_YES'),
                    'windowContent' => $this->language->get('L_WINDOW')[$get->get('process')],
                    'status' => 'ok'
                ]);
            break;

            case 'Post/Report':
            case 'Topic/Report':
            case 'ProfilePost/Report':
            case 'ProfilePostComment/Report':
            
                $this->data->data([
                    'windowTitle' => $this->language->get('L_WINDOW_REPORT_TITLE'),
                    'windowClose' => $this->language->get('L_CANCEL'),
                    'windowSubmit' => $this->language->get('L_SUBMIT'),
                    'windowContent' => $this->language->get('L_WINDOW_REPORT_DESC') . '<br><textarea></textarea>',
                    'status' => 'ok'
                ]);
            break;
        }
    }
}