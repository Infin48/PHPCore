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

namespace Process\Admin\Report;

/**
 * Close
 */
class Close extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'report_id'
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
        $this->db->query('
            UPDATE ' . TABLE_REPORTS . '
            SET report_status = 1
            WHERE report_id = ?
        ', [$this->data->get('report_id')]);

        $this->db->insert(TABLE_REPORTS_REASONS, [
            'user_id' => LOGGED_USER_ID,
            'report_id' => $this->data->get('report_id'),
            'report_reason_type' => (int)1
        ]);

        // ADD RECORD TO LOG
        $this->redirect('/admin/report/');
        
        // ADD RECORD TO LOG
        $this->log();
    }
}