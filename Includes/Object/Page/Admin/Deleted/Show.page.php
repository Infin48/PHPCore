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

namespace App\Page\Admin\Deleted;

/**
 * Show
 */
class Show extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;
    
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
            'run/deleted-post/move-back' => 'movePostBack',
            'run/deleted-topic/move-back' => 'moveTopicBack',
            'run/deleted-profile-post/move-back' => 'moveProfilePostBack',
            'run/deleted-profile-post-comment/move-back' => 'moveProfilePostCommentBack',

            'run/deleted-post/delete' => 'deleteDeletedPost',
            'run/deleted-topic/delete' => 'deleteDeletedTopic',
            'run/deleted-profile-post/delete' => 'deleteDeletedProfilePost',
            'run/deleted-profile-post-comment/delete' => 'deleteDeletedProfilePostComment',

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
            'run/deleted-post/move-back',
            'run/deleted-topic/move-back',
            'run/deleted-profile-post/move-back',
            'run/deleted-profile-post-comment/move-back',

            'run/deleted-post/delete',
            'run/deleted-topic/delete',
            'run/deleted-profile-post/delete',
            'run/deleted-profile-post-comment/delete' => [
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

        // Language
        $language = $data->get('inst.language');
        
        // If forum is not enabled
		if (!in_array($system->get('site_mode'), ['forum', 'blog_with_forum']))
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('deleted')->active();
        
        // Delete data
        $row = $db->select('app.deleted.get()', $this->url->getID()) or $this->error404();

        // Save data about deleted content
        $data->set('data.content', $row);

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Deleted.json');
        $form
            ->form('deleted')
                ->callOnSuccess($this, 'deleteContent')
                ->disButtons();

        // Assign data based on type
        switch ($data->get('data.content.deleted_type'))
        {
            // Post
            case 'Post':
                $type = 'post';
                $elm3 = 'post_id';
            break;

            // Topic
            case 'Topic':
                $type = 'topic';
                $elm3 = 'topic_id';
            break;

            // Profile post
            case 'ProfilePost':
                $type = 'profile-post';
                $elm3 = 'profile_post_id';
            break;

            // Comment under profile post
            case 'ProfilePostComment':
                $type = 'profile-post-comment';
                $elm3 = 'profile_post_comment_id';
            break;
        }

        // Load data about deleted content
        $content = $db->select('app.' . $type . '.get()', $data->get('data.content.deleted_type_id'), true);

        // If data are empty
        if (empty($content))
        {
            // Show error page
            $this->error404();
        }

        // Fill form with all data
        $form
            ->data(array_merge($content, $data->get('data.content')))
            // Show correct content ID
            ->frame('show')
                ->set('data.html.ajax-item', 'deleted-' . $type)
                ->set('data.html.ajax-id', $data->get('data.content.deleted_id'))
                ->input($elm3)
                    ->show()
                    ->set('data.value', $content[$elm3])
                ->input('deleted')
                    ->set('data', array_merge($form->get('data'), $content))
                // Url to deleted content
                ->input('show')
                    ->set('data.href', '$' . $this->build->url->{lcfirst($data->get('data.content.deleted_type'))}($content));

        // Save form and get ready to generate
        $data->form = $form->getDataToGenerate();

        // Block
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Deleted/Show.json');
        $block
            // Set content type
            ->elm1('type')->value($language->get('L_CONTENT_LIST.' . $data->get('data.content.deleted_type')))
            // Set record ID
            ->elm1('id')->value($data->get('data.content.deleted_id'))
            // Set date of deletion
            ->elm1('deleted')->value($this->build->date->long($data->get('data.content.deleted_created')))
            // Set name of user who deleted this content
            ->elm1('deleted_by')->value($data->get('data.content.user_name'));

        // Save block and get ready to generate
        $data->block = $block->getDataToGenerate();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Deleted.json');
        $breadcrumb->create()->jumpTo()->title('L_CONTENT_LIST.' . $data->get('data.content.deleted_type'))->href('/admin/deleted/show/' . $data->get('data.content.deleted_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();
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
    public function deleteDeletedPost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Delete post
        $db->query('
            DELETE dc, p, pl, r, rr
            FROM ' . TABLE_DELETED_CONTENT. ' 
            LEFT JOIN ' . TABLE_POSTS . ' ON p.post_id = dc.deleted_type_id
            LEFT JOIN ' . TABLE_POSTS_LIKES . ' ON pl.post_id = p.post_id
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = p.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . ' ON rr.report_id = r.report_id
            WHERE dc.deleted_id = ?
        ', [$post->get('id')]);

        // Increment deleted posts by 1
        $db->stats([
            'post_deleted' => + 1
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect back
        $data->set('data.redirect', '/admin/deleted/');
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
    public function movePostBack( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move post back to forum
        $db->query('
            UPDATE ' . TABLE_DELETED_CONTENT . '
            LEFT JOIN ' . TABLE_POSTS . ' ON p.post_id = dc.deleted_type_id
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET p.deleted_id = NULL, t.topic_posts = t.topic_posts + 1, f.forum_posts = f.forum_posts + 1
            WHERE dc.deleted_id = ?
        ', [$post->get('id')]);

        // Delete post from deleted content
        $db->query('
            DELETE dc FROM ' . TABLE_DELETED_CONTENT . '
            WHERE deleted_id = ?
        ', [$post->get('id')]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect back
        $data->set('data.redirect', '/admin/deleted/');
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
    public function deleteDeletedTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get number of posts
        $posts = (int)$db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_POSTS . '
            WHERE p.topic_id = ?
        ', [$data->get('data.content.deleted_type_id')])['count'] ?? 0;

        // Delete topic
        $db->query('
            DELETE dc, t, tl, tlb, r, rr, p, pl, r2, rr2, dc2
            FROM ' . TABLE_DELETED_CONTENT . '
            LEFT JOIN ' . TABLE_TOPICS. ' ON t.topic_id = dc.deleted_type_id
            LEFT JOIN ' . TABLE_TOPICS_LIKES . ' ON tl.topic_id = t.topic_id 
            LEFT JOIN ' . TABLE_TOPICS_LABELS . ' ON tlb.topic_id = t.topic_id 
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = t.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . ' ON rr.report_id = r.report_id

            LEFT JOIN ' . TABLE_POSTS . ' ON p.topic_id = t.topic_id 
            LEFT JOIN ' . TABLE_POSTS_LIKES . ' ON pl.post_id = p.post_id
            LEFT JOIN ' . TABLE_REPORTS . '2 ON r2.report_id = p.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . '2 ON rr2.report_id = r2.report_id
            LEFT JOIN ' . TABLE_DELETED_CONTENT . '2 ON dc2.deleted_id = p.deleted_id
            WHERE dc.deleted_id = ?
        ', [$post->get('id')]);

        // Update statistics
        $db->stats([
            'topic_deleted' => + 1,
            'post_deleted' => + $posts
        ]);

        // File model
        $file = new \App\Model\File\File();

        // Delete image
        $file->delete('/Uploads/Topics/' . $data->get('data.content.deleted_type_id') . '/*');

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect back
        $data->set('data.redirect', '/admin/deleted/');
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
    public function moveTopicBack( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move topic back to forum
        $db->query('
            UPDATE ' . TABLE_DELETED_CONTENT . '
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = dc.deleted_type_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET t.deleted_id = NULL, f.forum_topics = f.forum_topics + 1, f.forum_posts = f.forum_posts + t.topic_posts
            WHERE dc.deleted_id = ?
        ', [$post->get('id')]);

        $db->query('
            DELETE dc FROM ' . TABLE_DELETED_CONTENT . '
            WHERE deleted_id = ?
        ', [$post->get('id')]);
        
        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Redirect back
        $data->set('data.redirect', '/admin/deleted/');
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
    public function deleteDeletedProfilePost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get number of profile post comments
        $comments = (int)$db->query('
            SELECT COUNT(*) as count
            FROM ' . TABLE_PROFILE_POSTS_COMMENTS . '
            LEFT JOIN ' . TABLE_PROFILE_POSTS . ' ON pp.profile_post_id = ppc.profile_post_id
            LEFT JOIN ' . TABLE_DELETED_CONTENT . ' ON dc.deleted_id = pp.deleted_id
            WHERE dc.deleted_id = ?
        ', [$post->get('id')])['count'] ?? 0;

        // Delete profile psot and comments
        $db->query('
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
        ', [$post->get('id')]);

        // Update statistics
        $db->stats([
            'profile_post_deleted' => + 1,
            'profile_post_comment_deleted' => +($comments)
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect back
        $data->set('data.redirect', '/admin/deleted/');
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
    public function moveProfilePostBack( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move profile post back to profile
        $db->query('
            UPDATE ' . TABLE_DELETED_CONTENT . '
            LEFT JOIN ' . TABLE_PROFILE_POSTS . ' ON pp.profile_post_id = dc.deleted_type_id
            SET pp.deleted_id = NULL
            WHERE dc.deleted_id = ?
        ', [$post->get('id')]);

        // Delete profile psot from deleted content
        $db->query('
            DELETE dc FROM ' . TABLE_DELETED_CONTENT . '
            WHERE deleted_id = ?
        ', [$post->get('id')]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Redirect back
        $data->set('data.redirect', '/admin/deleted/');
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
    public function deleteDeletedProfilePostComment( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Delete profile psot comment
        $db->query('
            DELETE dc, ppc, r, rr
            FROM ' . TABLE_DELETED_CONTENT. ' 
            LEFT JOIN ' . TABLE_PROFILE_POSTS_COMMENTS . ' ON ppc.profile_post_comment_id = dc.deleted_type_id
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = ppc.report_id
            LEFT JOIN ' . TABLE_REPORTS_REASONS . ' ON rr.report_id = r.report_id
            WHERE dc.deleted_id = ?
        ', [$post->get('id')]);

        // Update statistics
        $db->stats([
            'profile_post_comment_deleted' => + 1
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect back
        $data->set('data.redirect', '/admin/deleted/');
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
    public function moveProfilePostCommentBack( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move profile post comment back to profile
        $db->query('
            UPDATE ' . TABLE_DELETED_CONTENT . '
            LEFT JOIN ' . TABLE_PROFILE_POSTS_COMMENTS . ' ON ppc.profile_post_comment_id = dc.deleted_type_id
            SET ppc.deleted_id = NULL
            WHERE dc.deleted_id = ?
        ', [$post->get('id')]);

        $db->query('
            DELETE dc FROM ' . TABLE_DELETED_CONTENT . '
            WHERE deleted_id = ?
        ', [$post->get('id')]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect back
        $data->set('data.redirect', '/admin/deleted/');
    }
}