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

/**
 * Language
 */
class Language extends Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {   
        $this->templateName = 'Language';

        $languages = [];

        // LOADS LANGUAGES FROM FOLDER
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

        $this->data->data([
            'languages' => $languages
        ]);

        // SET LANGUAGE
        $this->process->form(type: 'Language', data: [
            'languageList' => array_column($languages, 'iso')
        ]);
    }
}