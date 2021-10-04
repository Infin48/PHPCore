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

namespace Process\Admin\Deleted\ProfilePost;

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
        $comments = (int)$this->db->query('
            SELECT COUNT(*) as count
            FROM ' . TABLE_PROFILE_POSTS_COMMENTS . '
            LEFT JOIN ' . TABLE_PROFILE_POSTS . ' ON pp.profile_post_id = ppc.profile_post_id
            LEFT JOIN ' . TABLE_DELETED_CONTENT . ' ON dc.deleted_id = pp.deleted_id
            WHERE dc.deleted_id = ?
        ', [$this->data->get('deleted_id')])['count'] ?? 0;

        $this->db->query('
            DELETE dc, pp, r, rr, ppc, dc2, r2, rr2
            FROM ' . TABLE_DELETED_CONTENT. ' 
            LEFT JOIN ' . TABLE_PROFILE_POSTS . ' ON pp.profile_post_id = dc.deleted_type_id
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = pp.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . ' ON rr.report_id = r.report_id
            LEFT JOIN ' . TABLE_PROFILE_POSTS_COMMENTS . ' ON ppc.profile_post_id = pp.profile_post_id
            LEFT JOIN ' . TABLE_DELETED_CONTENT . '2 ON dc2.deleted_id = ppc.deleted_id
            LEFT JOIN ' . TABLE_REPORTS . '2 ON r2.report_id = ppc.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . '2 ON rr2.report_id = r2.report_id
            WHERE dc.deleted_id = ?
        ', [$this->data->get('deleted_id')]);

        $this->db->stats([
            'profile_post_deleted' => + 1,
            'profile_post_comment_deleted' => +($comments)
        ]);

        // ADD RECORD TO LOG
        $this->log();

        $this->redirect('/admin/deleted/');
    }
}