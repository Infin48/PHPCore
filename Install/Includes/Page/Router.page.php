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

use Model\JSON;
use Model\Data;
use Model\Language;

use Process\Process;

use Style\Style;

/**
 * Router
 */
class Router extends Page
{    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body()
    {
        $JSON = new JSON('/Install/Includes/Settings.json');

        $this->style = new Style();
        $this->style->setTemplate('/Body');

        switch ($JSON->get('page')) {


            case 'database':

                $this->page = new \Page\Database();

            break;


            case 'extensions':

                $this->page = new \Page\Extensions();

            break;


            case 'write':

                $this->page = new \Page\Write();

            break;


            case 'index':

                $this->page = new \Page\Index();

            break;


            case 'update':

                $this->page = new \Page\Update\Update();

            break;


            case 'install-language':

                $this->page = new \Page\Install\Language();

            break;


            case 'install-admin':

                $this->page = new \Page\Install\Admin();

            break;


            case 'install-site':

                $this->page = new \Page\Install\Site();

            break;


            case 'end':

                if ($JSON->get('operation') === 'install') {

                    $this->page = new \Page\Install\End();
                } else {

                    $this->page = new \Page\Update\End();
                }

            break;
        }

        $this->data = new Data();
        $this->data->data = [
            'page' => $JSON->get('page'),
            'back' => $JSON->get('back'),
            'operation'=> $JSON->get('operation')
        ];

        $this->language = new Language();
        $this->process = new Process();

        $this->language->load('/Languages/' . ($JSON->get('language') ?? 'cs') . '/Install');

        if (isset($_GET['go'])) {

            $JSON->set('back', false);

            switch ($_GET['go']) {


                case 'back':

                    $JSON->set('back', true);

                    switch ($JSON->get('page')) {


                        case 'extensions':

                            $JSON->set('page', 'index');
                        break;


                        case 'write':

                            $JSON->set('page', 'index');
                        break;


                        case 'database':

                            if ($JSON->get('operation') === 'install') {

                                $JSON->set('page', 'install-language');
                            } else {

                                $JSON->set('page', 'index');
                            }
                        break;


                        case 'update':

                            $JSON->set('page', 'database');
                        break;


                        case 'install-admin':

                            $JSON->set('page', 'database');
                        break;


                        case 'install-language':

                            $JSON->set('page', 'index');
                        break;


                        case 'install-site':

                            $JSON->set('page', 'install-admin');
                        break;
                    }
                break;


                case 'repeat':

                    $JSON->set('db', false);
                    $JSON->set('page', 'menu');
                    $JSON->set('operation', '');
                break;


                case 'index':

                    $JSON->set('db', false);
                    $JSON->set('page', 'index');
                    $JSON->set('operation', '');
                break;


                case 'install':

                    $JSON->set('page', 'install-language');
                    $JSON->set('operation', 'install');
                break;


                case 'update':

                    $JSON->set('page', 'database');
                    $JSON->set('operation', 'update');
                break;


                case 'extensions':

                    $JSON->set('page', 'extensions');
                break;

                
                case 'write':

                    $JSON->set('page', 'write');
                break;


                case 'installUpdate':

                    define('AJAX', true);

                    $update = new \Page\Update\Ajax\Update();
                    $update->body();
                break;
            }

            $JSON->save();

            refresh();
        }

        // PAGE
        $this->page->data = $this->data;
        $this->page->style = $this->style;
        $this->page->process = $this->process;
        $this->page->language = $this->language;

        $this->page->ini();
        $this->page->body();

        // STYLE
        $this->style->data = $this->data;
        $this->style->language = $this->language;

        $this->style->show();
    }
}