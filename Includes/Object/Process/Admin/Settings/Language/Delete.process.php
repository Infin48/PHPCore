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

namespace Process\Admin\Settings\Language;

use Model\File\File;

/**
 * Delete
 */
class Delete extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'language_name_folder'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if ($this->data->get('language_name_folder') == $this->system->get('site.language')) {
            return false;
        }

        $file = new File();
        $file->delete(ROOT . '/Languages/' . $this->data->get('language_name_folder'));

        // ADD RECORD TO LOG
        $this->log($this->data->get('language_name_folder'));

        // REFRESH PAGE
        $this->refresh();
    }
}