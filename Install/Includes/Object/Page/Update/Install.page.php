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

namespace App\Page\Update;

/**
 * Install
 */
class Install extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database $db )
    {
        $db = new \App\Model\Database(true);

        $db->file('/Install/Update.sql');

        foreach ($db->query('SELECT * FROM phpcore_articles', [], ROWS) as $article)
        {
            if (!is_numeric(explode('.', $article['article_url'])[0]))
            {
                $url = $article['article_id'] . '.' . $article['article_url'];
                $db->query('UPDATE phpcore_articles SET article_url = ? WHERE article_id = ?', [$url, $article['article_id']]);
            }
        }

        foreach ($db->query('SELECT * FROM phpcore_topics', [], ROWS) as $topic)
        {
            if (!is_numeric(explode('.', $topic['topic_url'])[0]))
            {
                $url = $topic['topic_id'] . '.' . $topic['topic_url'];
                $db->query('UPDATE phpcore_topics SET topic_url = ? WHERE topic_id = ?', [$url, $topic['topic_id']]);
            }
        }

        foreach ($db->query('SELECT * FROM phpcore_conversations', [], ROWS) as $conversation)
        {
            if (!is_numeric(explode('.', $conversation['conversation_url'])[0]))
            {
                $url = $conversation['conversation_id'] . '.' . $conversation['conversation_url'];
                $db->query('UPDATE phpcore_conversations SET conversation_url = ? WHERE conversation_id = ?', [$url, $conversation['conversation_id']]);
            }
        }

        if ($db->query('SHOW TABLES LIKE "phpcore_categories_permission_see"'))
        {
            $arr = [];
            foreach ($db->query('SELECT * FROM phpcore_categories_permission_see', [], ROWS) as $perm)
            {
                $arr[$perm['category_id']] ??= [];
                $arr[$perm['category_id']][] = $perm['group_id'];
            }

            foreach ($arr as $id => $groups)
            {
                $db->insert(TABLE_CATEGORIES_PERMISSION, [
                    'category_id' => $id,
                    'permission_see' => implode(',', $groups)
                ]);
            }
        }

        $arr = [];
        if ($db->query('SHOW TABLES LIKE "phpcore_forums_permission_see"'))
        {
            foreach ($db->query('SELECT * FROM phpcore_forums_permission_see', [], ROWS) as $perm)
            {
                $arr[$perm['forum_id']] ??= [];
                $arr[$perm['forum_id']]['see'] ??= [];
                $arr[$perm['forum_id']]['see'][] = $perm['group_id'];
            }
        }

        if ($db->query('SHOW TABLES LIKE "phpcore_forums_permission_post"'))
        {
            foreach ($db->query('SELECT * FROM phpcore_forums_permission_post', [], ROWS) as $perm)
            {
                $arr[$perm['forum_id']] ??= [];
                $arr[$perm['forum_id']]['post'] ??= [];
                $arr[$perm['forum_id']]['post'][] = $perm['group_id'];
            }
        }

        if ($db->query('SHOW TABLES LIKE "phpcore_forums_permission_topic"'))
        {
            foreach ($db->query('SELECT * FROM phpcore_forums_permission_topic', [], ROWS) as $perm)
            {
                $arr[$perm['forum_id']] ??= [];
                $arr[$perm['forum_id']]['topic'] ??= [];
                $arr[$perm['forum_id']]['topic'][] = $perm['group_id'];
            }
        }

        foreach ($arr as $id => $groups)
        {
            $db->insert(TABLE_FORUMS_PERMISSION, [
                'forum_id' => $id,
                'permission_see' => implode(',', $groups['see'] ?? []),
                'permission_post' => implode(',', $groups['post'] ?? []),
                'permission_topic' => implode(',', $groups['topic'] ?? [])
            ]);
        }

        $db->drop('phpcore_categories_permission_see');
        $db->drop('phpcore_forums_permission_see');
        $db->drop('phpcore_forums_permission_post');
        $db->drop('phpcore_forums_permission_topic');

        $file = new \App\Model\File();
        $file->delete('/Includes/Object/Page/Admin/Sync/*');
        $file->delete('/Includes/Object/Page/Profile.page.php');
        $file->mkdir('/Plugins');

        @rename(ROOT . '/Uploads/Topic/', ROOT . '/Uploads/Topics/');
        @rename(ROOT . '/Uploads/User/', ROOT . '/Uploads/Users/');

        $db->table(TABLE_SETTINGS, [
            'session.scripts' => mt_rand(),
            'session.labels' => mt_rand(),
            'session.groups' => mt_rand(),
            'site.template' => 'Default',
            'site.updated' => DATE_DATABASE,
        ]);

        echo json_encode([
            'status' => 'ok'
        ]);

        exit();
    }
}