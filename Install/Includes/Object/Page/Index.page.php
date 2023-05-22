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

namespace App\Page;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = '/Language.phtml';


    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database $db )
    {        
        $languages = [];

        // Loads languages from folder
        foreach (glob(ROOT . '/Languages/*', GLOB_ONLYDIR) as $dir) {
            if (!file_exists($dir . '/Info.json')) continue;
            if (!file_exists($dir . '/Install/Load.language.php')) continue;

            $iso = explode('/', $dir)[count(explode('/', $dir)) - 1];
            $json = json_decode(file_get_contents($dir . '/Info.json'), true);
            $languages[] = [
                'iso' => $iso,
                'name' => $json['name']
            ];
        }

        $data->set('data.languages', $languages);

        $form = new \App\Visualization\Form\Form('/Language.json');
        $form->callOnSuccess($this, 'setupLanguage');
        $data->form = $form;
    }

    public function setupLanguage( \App\Model\Data $data, \App\Model\Database $db, \App\Model\Post $post )
    {
        $JSON = new \App\Model\JSON('/Install/Includes/Settings.json');
        $JSON->set('db', false);
        $JSON->set('language', $post->get('language'));
        $JSON->save();

        redirect('/menu/');
    }
}