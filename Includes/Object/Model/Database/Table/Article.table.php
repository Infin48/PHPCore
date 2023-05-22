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
 * Article
 */
class Article extends Table
{   
    /**
     * Returns article
     *
     * @param  int $ID Article ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        $article =  $this->db->query('
            SELECT a.*, ( SELECT COUNT(*) FROM ' . TABLE_ARTICLES_LABELS . ' WHERE article_id = a.article_id ) AS count_of_labels, ' . $this->select->user(role: true) . '
            FROM ' . TABLE_ARTICLES . '
            ' . $this->join->user(on: 'a.user_id', role: true) . '
            WHERE article_id = ?
        ', [$ID]);

        if (!$article)
        {
            return [];
        }

        $article['labels'] = [];
        if ($article['count_of_labels'] >= 1)
        {
            $article['labels'] = $this->getLabels($ID);
        }

        return $article;
    }
    
    /**
     * Returns all articles 
     *
     * @return array
     */
    public function all()
    {
        $articles = $this->db->query('
            SELECT a.*, ( SELECT COUNT(*) FROM ' . TABLE_ARTICLES_LABELS . ' WHERE article_id = a.article_id ) AS count_of_labels, ' . $this->select->user(role: true) . '
            FROM ' . TABLE_ARTICLES . '
            ' . $this->join->user(on: 'a.user_id', role: true) . '
            ORDER BY article_sticked DESC, article_created DESC
            LIMIT ?, ?
        ', [$this->pagination['offset'], $this->pagination['max']], ROWS);

        foreach ($articles as &$_)
        {
            if ($_['count_of_labels'] >= 1)
            {
                $_['labels'] = $this->getLabels($_['article_id']) ?: [];
            }
        }
        return $articles;
    }

    /**
     * Returns articles with given label
     *
     * @param  int $labelID Label ID
     * 
     * @return array
     */
    public function label( int $labelID )
    {
        $articles = $this->db->query('
            SELECT a.*, ( SELECT COUNT(*) FROM ' . TABLE_ARTICLES_LABELS . ' WHERE article_id = a.article_id ) AS count_of_labels, ' . $this->select->user(role: true) . '
            FROM ' . TABLE_ARTICLES . '
            ' . $this->join->user(on: 'a.user_id', role: true) . '
            JOIN ' . TABLE_ARTICLES_LABELS . ' ON alb.article_id = a.article_id AND alb.label_id = ?
            ORDER BY article_created DESC
            LIMIT ?, ?
        ', [$labelID, $this->pagination['offset'], $this->pagination['max']], ROWS);

        foreach ($articles as &$_)
        {
            $_['labels'] = $this->getLabels($_['article_id']) ?: [];
        }
        return $articles;
    }

    /**
     * Returns count of articles with given label
     *
     * @param  int $labelID Label ID
     * 
     * @return int
     */
    public function labelCount( int $labelID )
    {
        return (int)$this->db->query('
            SELECT COUNT(*) as count
            FROM ' . TABLE_ARTICLES . '
            JOIN ' . TABLE_ARTICLES_LABELS . ' ON alb.article_id = a.article_id AND alb.label_id = ?
        ', [$labelID])['count'];
    }

    /**
     * Returns labels from article
     *
     * @param  int $ID Article ID
     * 
     * @return array
     */
    public function getLabels( int $ID )
    {
        return $this->db->query('
            SELECT label_name, label_class, l.label_id
            FROM ' . TABLE_ARTICLES_LABELS . '
            LEFT JOIN ' . TABLE_LABELS . ' ON l.label_id = alb.label_id
            WHERE alb.article_id = ?
            ORDER BY l.position_index DESC
        ', [$ID], ROWS);
    }

    /**
     * Returns count of articles
     * 
     * @return int
     */
    public function count()
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_ARTICLES
        )['count'];
    }

    /**
     * Returns last three articles except given
     *
     * @param  int $ID Article ID
     * 
     * @return array
     */
    public function lastExcept( int $ID )
    {
        $articles = $this->db->query('
            SELECT a.*, ( SELECT COUNT(*) FROM ' . TABLE_ARTICLES_LABELS . ' WHERE article_id = a.article_id ) AS count_of_labels, ' . $this->select->user(role: true) . '
            FROM ' . TABLE_ARTICLES . '
            ' . $this->join->user(on: 'a.user_id', role: true) . '
            WHERE article_id <> ?
            ORDER BY article_created DESC
            LIMIT 3
        ', [$ID], ROWS);

        foreach ($articles as &$_)
        {
            if ($_['count_of_labels'] >= 1)
            {
                $_['labels'] = $this->getLabels($_['article_id']) ?: [];
            }
        }
        return $articles;
    }
}