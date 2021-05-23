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

namespace Process\Admin\Forum;

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
            'forum_id'
        ],
        'block' => [
            'forum_name'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Admin\Forum',
            'method' => 'get',
            'selector' => 'forum_id'
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
            UPDATE ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS . '2 ON f2.position_index > f.position_index AND f2.category_id = f.category_id
            SET f2.position_index = f2.position_index - 1
            WHERE f.forum_id = ?
        ', [$this->data->get('forum_id')]);

        $stats = $this->db->query('
            SELECT (
                SELECT COUNT(*)
                FROM ' . TABLE_POSTS . '
                WHERE p.forum_id = ?
            ) as posts, (
                SELECT COUNT(*)
                FROM ' . TABLE_TOPICS . '
                WHERE t.forum_id = ?
            ) as topics
        ', [$this->data->get('forum_id'), $this->data->get('forum_id')]);

        $this->db->query('
            DELETE f, fpp, fps, fpt, t, tl, tlb, r, rr, dc, p, pl, r2, rr2, dc2
            FROM ' . TABLE_FORUMS. ' 
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_POST . ' ON fpp.forum_id = f.forum_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_TOPIC . ' ON fpt.forum_id = f.forum_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.forum_id = f.forum_id
            LEFT JOIN ' . TABLE_TOPICS_LIKES . ' ON tl.topic_id = t.topic_id
            LEFT JOIN ' . TABLE_TOPICS_LABELS . ' ON tlb.topic_id = t.topic_id
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = t.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . ' ON rr.report_id = r.report_id
            LEFT JOIN ' . TABLE_DELETED_CONTENT . ' ON dc.deleted_id = t.deleted_id
            LEFT JOIN ' . TABLE_POSTS . ' ON p.topic_id = t.topic_id
            LEFT JOIN ' . TABLE_POSTS_LIKES . ' ON pl.post_id = p.post_id 
            LEFT JOIN ' . TABLE_REPORTS . '2 ON r2.report_id = p.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . '2 ON rr2.report_id = r2.report_id
            LEFT JOIN ' . TABLE_DELETED_CONTENT . '2 ON dc2.deleted_id = p.deleted_id
            WHERE f.forum_id = ' . $this->data->get('forum_id') . '
        ');

        $this->system->stats->set('post_deleted', +((int)$stats['posts'] ?? 0));
        $this->system->stats->set('topic_deleted', +((int)$stats['topics'] ?? 0));

        // ADD RECORD TO LOG
        $this->log($this->data->get('forum_name'));
    }
}