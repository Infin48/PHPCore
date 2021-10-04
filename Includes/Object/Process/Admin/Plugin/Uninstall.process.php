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
 * Uninstall
 */
class Uninstall extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'plugin_id'
        ],
        'block' => [
            'plugin_name_folder'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Plugin',
            'method' => 'get',
            'selector' => 'plugin_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->query('DELETE plg FROM ' . TABLE_PLUGINS . ' WHERE plugin_id = ?', [$this->data->get('plugin_id')]);

        require ROOT . '/Plugins/' . $this->data->get('plugin_name_folder') . '/Uninstall.plugin.php';

        // ADD RECORD TO LOG
        $this->log($this->data->get('plugin_name_folder'));

        // REFRESH PAGE
        $this->refresh();
    }
}