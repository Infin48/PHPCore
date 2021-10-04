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
 * Back
 */
class Back extends \Process\ProcessExtend
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
            UPDATE ' . TABLE_DELETED_CONTENT . '
            LEFT JOIN ' . TABLE_POSTS . ' ON p.post_id = dc.deleted_type_id
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET p.deleted_id = NULL, t.topic_posts = t.topic_posts + 1, f.forum_posts = f.forum_posts + 1
            WHERE dc.deleted_id = ?
        ', [$this->data->get('deleted_id')]);

        $this->db->query('
            DELETE dc FROM ' . TABLE_DELETED_CONTENT . '
            WHERE deleted_id = ?
        ', [$this->data->get('deleted_id')]);

        // ADD RECORD TO LOG
        $this->log();

        $this->redirect('/admin/deleted/');
    }
}