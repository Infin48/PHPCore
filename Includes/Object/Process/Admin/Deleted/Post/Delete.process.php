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

namespace Process\Admin\Deleted\Post;

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
            'deleted_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Deleted',
            'method' => 'get',
            'selector' => 'deleted_id'
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
            DELETE dc, p, pl, r, rr
            FROM ' . TABLE_DELETED_CONTENT. ' 
            LEFT JOIN ' . TABLE_POSTS . ' ON p.post_id = dc.deleted_type_id
            LEFT JOIN ' . TABLE_POSTS_LIKES . ' ON pl.post_id = p.post_id
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = p.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . ' ON rr.report_id = r.report_id
            WHERE dc.deleted_id = ?
        ', [$this->data->get('deleted_id')]);

        $this->system->stats->set('post_deleted', +1);

        // ADD RECORD TO LOG
        $this->log();

        $this->redirectTo('/admin/deleted/');
    }
}