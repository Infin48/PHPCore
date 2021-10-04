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

namespace Process\Admin\Plugin;

use Block\Plugin;

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
            'plugin_name_folder'
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
        $plugin = new Plugin();

        if (in_array($this->data->get('plugin_name_folder'), array_column($plugin->getAll(), 'plugin_name_folder'))) {
            return false;
        }

        $file = new File();
        $file->delete(ROOT . '/Plugins/' . $this->data->get('plugin_name_folder'));

        // ADD RECORD TO LOG
        $this->log($this->data->get('plugin_name_folder'));

        // REFRESH PAGE
        $this->refresh();
    }
}