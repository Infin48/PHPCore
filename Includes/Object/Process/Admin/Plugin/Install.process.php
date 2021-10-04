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

/**
 * Install
 */
class Install extends \Process\ProcessExtend
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
        require ROOT . '/Plugins/' . $this->data->get('plugin_name_folder') . '/Install.plugin.php';
        
        $this->db->insert(TABLE_PLUGINS, [
            'plugin_name_folder' => $this->data->get('plugin_name_folder')
        ]);

        // ADD RECORD TO LOG
        $this->log($this->data->get('plugin_name_folder'));

        // REFRESH PAGE
        $this->refresh();
    }
}