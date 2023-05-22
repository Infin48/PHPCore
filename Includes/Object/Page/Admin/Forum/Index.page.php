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

namespace App\Page\Admin\Forum;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.forum';
    
    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    protected function ajax( string $ajax )
    {
        return match($ajax)
        {
            'run/category/up' => 'moveCategoryUp',
            'run/category/down' => 'moveCategoryDown',
            'run/category/delete' => 'deleteCategory',

            'run/forum/up' => 'moveForumUp',
            'run/forum/down' => 'moveForumDown',
            'run/forum/delete' => 'deleteForum',

            default => ''
        };
    }

    /**
     * Load data according to received ajax
     *
     * @param  string $ajax Received ajax
     * 
     * @return array Data
     */
    public function ajaxData( string $ajax )
    {
        return match($ajax)
        {
            'run/forum/up',
            'run/forum/delete',
            'run/category/delete',
            'run/forum/down' => [
                'id' => STRING
            ],

            default => []
        };
    }

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');
        
        // If forum is not enabled
		if ($system->get('site.mode') != 'forum')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('forum')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Forum.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // List of labels
        $categories = $db->select('app.category.all()');

        // Save list of category's ids
        $data->set('data.categories', array_column($categories, 'category_id'));

        // List with forum's ids
        $data->set('data.forums', []);
        
        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Forum.json');
        $list->elm1('forum')->fill(data: $categories, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count ) use ($db, $data)
        {
            $list
                ->set('data.id', $list->get('data.category_id'))    
                ->set('data.title', $list->get('data.category_name'))
                ->set('data.desc', $list->get('data.category_description'))
                ->set('data.html.ajax-id', $list->get('data.category_id'))
                // Set links to buttons
                ->set('data.button.add.href', '/admin/forum/add/' . $list->get('data.category_id'))
                ->set('data.button.edit.href', '/admin/category/show/' . $list->get('data.category_id'))
                ->set('data.button.permission.href', '/admin/category/permission/' . $list->get('data.category_id'));

            // Enable button to move category up on all categorys except first
            if ($i !== 1)
            {
                $list->enable('data.button.up');
            }

            // Enable button to move category down on all categorys except last
            if ($i !== $count)
            {
                $list->enable('data.button.down');
            }

            $main = 0;

            $forums = $db->select('app.forum.parent()', $list->get('data.category_id'));

            $data->set('data.forums', array_merge($data->get('data.forums'), array_column($forums, 'forum_id')));

            $list->fill(data: $forums, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count ) use (&$main)
            {
                $list
                    ->set('data.html.ajax-id', $list->get('data.forum_id'))
                    // Set links to buttons
                    ->set('data.button.edit.href', '/admin/forum/show/' . $list->get('data.forum_id'))
                    ->set('data.button.permission.href', '/admin/forum/permission/' .$list->get('data.forum_id'));

                if ($list->get('data.forum_main'))
                {
                    $main = 1;
                }

                // Enable button to move forum up on all forums except first
                if ($i !== 1)
                {
                    $list->enable('data.button.up');
                }
    
                // Enable button to move forum down on all forums except last
                if ($i !== $count)
                {
                    $list->enable('data.button.down');
                }
            });

            if ($main)
            {
                $list->delete('data.button.permission');
            }
        });
        $data->list = $list->getDataToGenerate();
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function deleteForum( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if label exists
        if (!in_array($post->get('id'), $data->get('data.forums')))
        {
            return false;
        }

        // Get number of posts and topics
        $stats = $db->query('
            SELECT (
                SELECT COUNT(*)
                FROM ' . TABLE_POSTS . '
                WHERE p.forum_id = ?
            ) as posts, (
                SELECT COUNT(*)
                FROM ' . TABLE_TOPICS . '
                WHERE t.forum_id = ?
            ) as topics
        ', [$post->get('id'), $post->get('id')]);
        
        // Move all previous forums one position down
        $db->query('
            UPDATE ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS . '2 ON f2.position_index > f.position_index AND f2.category_id = f.category_id
            SET f2.position_index = f2.position_index - 1
            WHERE f.forum_id = ?
        ', [$post->get('id')]);

        // Delete forum
        $db->query('
            DELETE f, fp, t, tl, tlb, r, rr, dc, p, pl, r2, rr2, dc2
            FROM ' . TABLE_FORUMS. ' 
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
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
            WHERE f.forum_id = ' . $post->get('id') . '
        ');

        // Update statistics
        $db->stats([
            'post_deleted' => + (int)$stats['posts'] ?? 0,
            'topic_deleted' => + (int)$stats['topics'] ?? 0
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function deleteCategory( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if category exists
        if (!in_array($post->get('id'), $data->get('data.categories')))
        {
            return false;
        }

        // Get number of posts and topics
        $stats = $db->query('
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
        ', [$post->get('id'), $post->get('id')]);

        // Update statistics
        $db->stats([
            'post_deleted' => + (int)$stats['posts'] ?? 0,
            'topic_deleted' => + (int)$stats['topics'] ?? 0
        ]);

        // Move all previous categories one position down
        $db->query('
            UPDATE ' . TABLE_CATEGORIES . '
            LEFT JOIN ' . TABLE_CATEGORIES . '2 ON c2.position_index > c.position_index
            SET c2.position_index = c2.position_index - 1
            WHERE c.category_id = ?
        ', [$post->get('id')]);

        // Delete category
        $db->query('
            DELETE c, cp, f, fp, t, tl, tlb, r, rr, dc, p, pl, r2, rr2, dc2
            FROM ' . TABLE_CATEGORIES. '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = c.category_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.category_id = c.category_id
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
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
            WHERE c.category_id = ' . $post->get('id') . '
        ');

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Refresh page
        $data->set('options.refresh', true);
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function moveForumUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if forum exists
        if (!in_array($post->get('id'), $data->get('data.forums')))
        {
            return false;
        }

        // Move forum one position up
        $db->query('
            UPDATE ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS . '2 ON f2.position_index = f.position_index + 1 AND f2.category_id = f.category_Id
            SET f.position_index = f.position_index + 1,
                f2.position_index = f2.position_index - 1
            WHERE f.forum_id = ?
        ', [$post->get('id')]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function moveForumDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if forum exists
        if (!in_array($post->get('id'), $data->get('data.forums')))
        {
            return false;
        }

        // Move forum one position down
        $db->query('
            UPDATE ' . TABLE_FORUMS . '
            LEFT JOIN ' . TABLE_FORUMS . '2 ON f2.position_index = f.position_index - 1 AND f2.category_id = f.category_Id
            SET f.position_index = f.position_index - 1,
                f2.position_index = f2.position_index + 1
            WHERE f.forum_id = ?
        ', [$post->get('id')]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function moveCategoryUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if category exists
        if (!in_array($post->get('id'), $data->get('data.categories')))
        {
            return false;
        }

        // Move category up
        $db->moveOnePositionUp( table: TABLE_CATEGORIES, id: $post->get('id') );

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function moveCategoryDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if category exists
        if (!in_array($post->get('id'), $data->get('data.categories')))
        {
            return false;
        }

        // Move category down
        $db->moveOnePositionDown( table: TABLE_CATEGORIES, id: $post->get('id') );

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }
}