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

namespace App\Table;

/**
 * News
 */
class News extends Table
{
    /**
     * Returns all news
     * 
     * @param  bool $deleted If true - also deleted content will be returned
     * 
     * @return array
     */
    public function all( bool $deleted = false )
    {
        $news = $this->db->query('
            SELECT ( SELECT COUNT(*) FROM ' . TABLE_TOPICS_LABELS . ' WHERE topic_id = t.topic_id ) AS count_of_labels, deleted_id, topic_id, topic_url, topic_edited_at, topic_image, topic_views, topic_text, topic_name, topic_created, topic_posts, topic_locked, topic_sticked, ' . $this->select->user(role: true) . ', topic_sticked
            FROM ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            ' . $this->join->user(on: 't.user_id', role: true). '
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = f.category_id
            WHERE f.forum_main = 1 ' . ($deleted === false ? 'AND t.deleted_id IS NULL' : '') . '
            ORDER BY topic_sticked DESC, topic_id DESC
            LIMIT ?, ?
        ', [$this->pagination['offset'], $this->pagination['max']], ROWS);

        foreach ($news as &$_)
        {
            if ($_['count_of_labels'] >= 1)
            {
                $_['labels'] = $this->getLabels($_['topic_id']) ?: [];
            }
        }
        return $news;
    }

    /**
     * Returns labels from topic
     *
     * @param  int $ID Topic ID
     * 
     * @return array
     */
    public function getLabels( int $ID )
    {
        return $this->db->query('
            SELECT label_name, label_class, l.label_id
            FROM ' . TABLE_TOPICS_LABELS . '
            LEFT JOIN ' . TABLE_LABELS . ' ON l.label_id = tlb.label_id
            WHERE tlb.topic_id = ?
            ORDER BY l.position_index DESC
        ', [$ID], ROWS);
    }

    /**
     * Returns count of news
     * 
     * @param  bool $deleted If true - deletec content will be also counted
     * 
     * @return int
     */
    public function count( bool $deleted = false )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            WHERE f.forum_main = 1 ' . ($deleted === false ? 'AND t.deleted_id IS NULL' : '') . '
        ')['count'];
    }
}