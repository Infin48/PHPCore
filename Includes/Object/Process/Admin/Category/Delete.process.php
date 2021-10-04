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

namespace Process\Admin\Category;

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
            'category_id'
        ],
        'block' => [
            'category_name'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'verify' => [
            'block' => '\Block\Admin\Category',
            'method' => 'get',
            'selector' => 'category_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $stats = $this->db->query('
            SELECT (
                SELECT COUNT(*)
                FROM ' . TABLE_POSTS . '
                LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = p.forum_id
                WHERE f.category_id = ?
            ) as posts, (
                SELECT COUNT(*)
                FROM ' . TABLE_TOPICS . '
                WHERE t.category_id = ?
            ) as topics
        ', [$this->data->get('category_id'), $this->data->get('category_id')]);

        // UPDATE STATISTICS
        $this->db->stats([
            'post_deleted' => + (int)$stats['posts'] ?? 0,
            'topic_deleted' => + (int)$stats['topics'] ?? 0
        ]);

        $this->db->query('
            UPDATE ' . TABLE_CATEGORIES . '
            LEFT JOIN ' . TABLE_CATEGORIES . '2 ON c2.position_index > c.position_index
            SET c2.position_index = c2.position_index - 1
            WHERE c.category_id = ?
        ', [$this->data->get('category_id')]);

        $this->db->query('
            DELETE c, cps, f, fps, fpp, fpt, t, tl, tlb, r, rr, dc, p, pl, r2, rr2, dc2
            FROM ' . TABLE_CATEGORIES. ' 
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION_SEE . ' ON cps.category_id = c.category_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.category_id = c.category_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_SEE . ' ON fps.forum_id = f.forum_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_POST . ' ON fpp.forum_id = f.forum_id 
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION_TOPIC . ' ON fpt.forum_id = f.forum_id
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
            WHERE c.category_id = ' . $this->data->get('category_id') . '
        ');

        // ADD RECORD TO LOG
        $this->log($this->data->get('category_name'));

        // REFRESH PAGE
        $this->refresh();
    }
}