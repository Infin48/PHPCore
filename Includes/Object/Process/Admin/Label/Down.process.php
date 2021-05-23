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

namespace Process\Admin\Label;

/**
 * Down
 */
class Down extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'label_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Label',
            'method' => 'get',
            'selector' => 'label_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->db->query('
            UPDATE ' . TABLE_LABELS . '
            LEFT JOIN ' . TABLE_LABELS . '2 ON l2.position_index = l.position_index - 1
            SET l.position_index = l.position_index - 1,
                l2.position_index = l2.position_index + 1
            WHERE l.label_id = ? AND l2.label_id IS NOT NULL
        ', [$this->data->get('label_id')]);

        // ADD RECORD TO LOG
        $this->log();
    }
}