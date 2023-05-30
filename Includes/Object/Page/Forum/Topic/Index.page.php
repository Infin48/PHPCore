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

namespace App\Page\Forum\Topic;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    public bool $editor = true;

    /**
     * @var bool $photoSwipe If true - JS library PhotoSwipe will be loaded 
     */
    protected bool $photoSwipe = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Forum/Topic/View.phtml';

    /**
     * @var bool $notification If true - notifications will be displayed
     */
    protected bool $notification = true;

    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    public function ajax( string $ajax )
    {
        return match($ajax)
        {
            'window/post/likes' => 'windowPostLikes',
            'window/topic/likes' => 'windowTopicLikes',

            'run/post/like' => 'likePost',
            'run/post/unlike' => 'unlikePost',
            'run/post/create' => 'newPost',
            'run/post/delete' => 'deletePost',
            'run/post/report' => 'reportPost',
            'run/post/editor' => 'editorPost',
            'run/post/edit' => 'editPost',

            'run/topic/like' => 'likeTopic',
            'run/topic/unlike' => 'unlikeTopic',
            'run/topic/label' => 'markTopicWithLabels',
            'run/topic/move' => 'moveTopic',
            'run/topic/lock' => 'lockTopic',
            'run/topic/unlock' => 'unlockTopic',
            'run/topic/stick' => 'stickTopic',
            'run/topic/unstick' => 'unstickTopic',
            'run/topic/delete' => 'deleteTopic',
            'run/topic/report' => 'reportTopic',

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
            'run/post/like',
            'run/post/unlike',
            'run/post/delete',
            'run/post/editor',
            'run/topic/lock',
            'run/topic/unlock',
            'run/topic/stick',
            'run/topic/unstick',
            'run/topic/delete',
            'run/topic/like',
            'run/topic/unlike',
            'window/post/likes',
            'window/topic/likes' => [
                'id' => STRING
            ],

            'run/post/create',
            'run/post/edit' => [
                'id' => STRING,
                'text' => STRING
            ],

            'run/post/report',
            'run/topic/report' => [
                'id' => STRING,
                'report_reason_text' => STRING
            ],

            'run/topic/label' => [
                'id' => STRING
            ],

            'run/topic/move' => [
                'id' => STRING,
                'forum_id' => STRING
            ],

            default => []
        };
    }

    /**
     * According to received ajax check if logged user has appropriate permission
     *
     * @param  string $ajax Received ajax
     * 
     * @return string|true Name of permission or true if user has to be logged in
     */
    public function ajaxPermission( string $ajax )
    {
        return match($ajax)
        {
            'run/topic/like',
            'run/topic/unlike',
            'run/topic/report',
            'run/post/like',
            'run/post/report',
            'run/post/unlike' => true,

            'run/topic/label' => 'topic.label',
            'run/topic/move' => 'topic.move',

            'run/topic/lock',
            'run/topic/unlock' => 'topic.lock',

            'run/topic/stick',
            'run/topic/unstick' => 'topic.stick',

            'run/topic/delete' => 'topic.delete',

            'run/post/create' => 'post.create',

            'run/post/delete' => 'post.delete',

            'run/post/editor',
            'run/post/edit' => 'post.edit',

            default => ''
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

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Language
        $language = $data->get('inst.language');

        // If is enabled blog mode
        if ($system->get('site.mode') == 'blog')
        {
            // Show 404 error page
            $this->error404();
        }

        // If logged user has permission to see deleted content
        $deleted = false;
        if ($permission->has('admin.forum'))
        {
            $deleted = true;
        }

        // Get topic data
        $row = $db->select('app.topic.get()', $this->url->getID(), $deleted) or $this->error404();

        // Save topic data
        $data->set('data.topic', $row);

        // If logged user doesn't have permission to see this topic
        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_see')))
        {
            $this->error404();
        }

		// File model
        $file = new \App\Model\File\File();

        $data->set('data.topic.images', []);
        // Search fimages
        $file->getFiles(
            path: '/Uploads/Topics/' . $this->url->getID() . '/Images/*',
            function: function ( \App\Model\File\File $file, string $path ) use ($data)
            {
                $size = getimagesize($path);
                $data->set('data.topic.images.' . mt_rand(), [
                    'path' => str_replace(ROOT, '', $path),

                    // Set default sizes for SVG images
                    'width' => $size[0] ?? 1920,
                    'height' => $size[1] ?? 1080
                ]);
            }
        );

        $data->set('data.topic.attachments', []);
        // Search attachments
        $file->getFiles(
            path: '/Uploads/Topics/' . $this->url->getID() . '/Attachments/*',
            flag: \App\Model\File\File::SORT_BY_DATE,
            function: function ( \App\Model\File\File $file, string $path ) use ($data)
            {
                $explode = array_filter(explode('/', str_replace(ROOT, '', $path)));

                $data->set('data.topic.attachments.' . mt_rand(), [
                    'name' => array_pop($explode),
                    'path' => str_replace(ROOT, '', $path)
                ]);
            }
        );

        // Notification
        $notification = new \App\Visualization\Notification\Notification($data->notification);

        // Topic is reported
        if ($data->get('data.topic.report_id'))
        {
            // Report is not closed
            if ($data->get('data.topic.report_status') == 0)
            {
                // User has permission to see reported content
                if ($permission->has('admin.forum'))
                {
                    $notification
                        // Create new object(notification) and jump inside
                        ->create()->jumpTo()
                        // Set name
                        ->set('data.name', 'topic-reported')
                        // Set type
                        ->set('data.type', 'notice')
                        // Set title
                        ->set('data.title', $language->get('L_NOTIFICATION.L_TOPIC_REPORTED.L_TITLE'))
                            // set icon to button
                            ->set('data.button.details.icon', 'fa-solid fa-circle-info')
                            // Set text to button
                            ->set('data.button.details.text', $language->get('L_DETAILS'))
                            // Set link to button
                            ->set('data.button.details.href', '/admin/report/show/' . $data->get('data.topic.report_id'))
                            // Back to root object
                            ->root();
                }
            }
        }

        // Topic is locked
        if ($data->get('data.topic.topic_locked') == 1)
        {
            $notification
                // Create new object(notification) and jump inside
                ->create()->jumpTo()
                // Set name
                ->set('data.name', 'topic-locked')
                // Set type
                ->set('data.type', 'notice')
                // Set title
                ->set('data.title', $language->get('L_NOTIFICATION.L_TOPIC_LOCKED.L_TITLE'))
                // Set icon
                ->set('data.icon', 'fa-solid fa-lock')
                // Back to root object
                ->root();
        }

        // Topic is deleted
        if ($data->get('data.topic.deleted_id'))
        {
            $notification
                // Create new object(notification) and jump inside
                ->create()->jumpTo()
                // Set name
                ->set('data.name', 'topic-deleted')
                // Set type
                ->set('data.type', 'warning')
                // Set title
                ->set('data.title', $language->get('L_NOTIFICATION.L_TOPIC_DELETED.L_TITLE'))
                // Set icon
                ->set('data.icon', 'fa-solid fa-trash')
                    // set icon to button
                    ->set('data.button.details.icon', 'fa-solid fa-circle-info')
                    // Set text to button
                    ->set('data.button.details.text', $language->get('L_DETAILS'))
                    // Set link to button
                    ->set('data.button.details.href', '/admin/deleted/show/' . $data->get('data.topic.deleted_id'))
                    // Back to root object
                    ->root();
        }

        // Finish notification and get ready for generate
        $data->notification = $notification->getDataToGenerate();

        // Set page title
        $data->set('data.head.title', $data->get('data.topic.topic_name'));

        // Set page description
        $data->set('data.head.description', $data->get('data.topic.topic_text'));

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Forum.json');
        $breadcrumb->elm1('category')->title($data->get('data.topic.category_name'))->up()
            ->create()->jumpTo()->title($data->get('data.topic.forum_name'))->href($this->build->url->forum($data->get('data.topic')))->up()
            ->create()->jumpTo()->title($data->get('data.topic.topic_name'))->href($this->build->url->topic( data: $data->get('data.topic')));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        if ($user->isLogged())
        {
            // Panel
            $panel = new \App\Visualization\Panel\Panel('Root/Panel:/Formats/Topic.json');
            $panel->id($this->url->getID());
            
            // Topic is not deleted
            if (!$data->get('data.deleted_id'))
            {
                // Show panel
                $panel->show();
            }
            
            $panel
                // Set position to tools dropdown
                ->elm1('tools')
                    // Edit topic button
                    ->elm2('edit', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
                    {
                        // Topic is not locked
                        if ($data->get('data.topic.topic_locked') == 0)
                        {
                            // Logged user has permission to manage topics in this forum
                            if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
                            {
                                // This topic is from logged user
                                if (LOGGED_USER_ID == $data->get('data.topic.user_id'))
                                {
                                    // Logged user has permission to edit topics
                                    if ($permission->has('topic.edit'))
                                    {
                                        // Show 'edit' button
                                        $panel->show();
                                    }
                                }
                            }
                        }
                    })
                    // Delete button
                    ->elm2('delete', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
                    {
                        // Topic is not locked
                        if ($data->get('data.topic.topic_locked') == 0)
                        {
                            // Topic is not deleted
                            if (!$data->get('data.topic.deleted_id'))
                            {
                                // Logged user has permission to manage topics in this forum
                                if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
                                {
                                    // If logged user has permission to delete topics
                                    if ($permission->has('topic.delete'))
                                    {
                                        // Show 'delete' button
                                        $panel->show();
                                    }
                                }
                            }
                        }
                    })
                    // Stick button
                    ->elm2('stick', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
                    {    
                        // If topic is not sticked
                        if ($data->get('data.topic.topic_sticked') == 0)
                        {
                            // Logged user has permission to manage topics in this forum
                            if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
                            {  
                                // Logged user has permission to stick topic
                                if ($permission->has('topic.stick'))
                                {
                                    // Show 'stick' button
                                    $panel->show();
                                }
                            }
                        }
                    })
                    // Unstick button
                    ->elm2('unstick', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
                    {
                        // If topic is not sticked
                        if ($data->get('data.topic.topic_sticked') == 1)
                        {
                            // Logged user has permission to manage topics in this forum
                            if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
                            {  
                                // Logged user has permission to stick topic
                                if ($permission->has('topic.stick'))
                                {
                                    // Show 'unstick' button
                                    $panel->show();
                                }
                            }
                        }
                    })
                    // Lock button
                    ->elm2('lock', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
                    {
                        // If topic is not sticked
                        if ($data->get('data.topic.topic_locked') == 0)
                        {
                            // Logged user has permission to manage topics in this forum
                            if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
                            {  
                                // Logged user has permission to lock topic
                                if ($permission->has('topic.lock'))
                                {
                                    // Show 'lock' button
                                    $panel->show();
                                }
                            }
                        }
                    })
                    // Unlock button
                    ->elm2('unlock', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
                    {
                        // If topic is not sticked
                        if ($data->get('data.topic.topic_locked') == 1)
                        {
                            // Logged user has permission to manage topics in this forum
                            if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
                            {  
                                // Logged user has permission to lock topic
                                if ($permission->has('topic.lock'))
                                {
                                    // Show 'unlock' button
                                    $panel->show();
                                }
                            }
                        }
                    })
                // Create new post button
                ->elm1('new', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $system, $permission)
                {
                    // Topic is not locked
                    if ($system->get('site.mode') != 'blog')
                    {
                        // Topic is not locked
                        if ($data->get('data.topic.topic_locked') == 0)
                        {
                            // Logged user has permission to manage posts in this forum
                            if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_post')))
                            { 
                                // If logged user has permission to create posts
                                if ($permission->has('post.create'))
                                {
                                    // Show 'create post' button
                                    $panel->show();
                                }
                            }
                        }
                    }
                })
                // Labels dropdown
                ->elm1('labels', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission, $db)
                {
                    $labels = $db->select('app.label.all()');
                    if ($labels)
                    {
                        // Topic is not locked
                        if ($data->get('data.topic.topic_locked') == 0)
                        {
                            // Topic is not deleted
                            if (!$data->get('data.topic.deleted_id'))
                            {
                                // Logged user has permission to manage topics in this forum
                                if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
                                {    
                                    // If logged user has permission to mark topic with labels
                                    if ($permission->has('topic.label'))
                                    {
                                        // Show 'labels' dropdown
                                        $panel->show()->fill( data: $labels, function: function ( \App\Visualization\Panel\Panel $panel ) use ($data)
                                        {
                                            if (in_array($panel->get('data.label_id'), array_column($data->get('data.topic.labels'), 'label_id')))
                                            {
                                                $panel->check();
                                            }
                                        });
                                    }
                                }
                            }
                        }
                    }
                })
                // Dropdown with forums twhere can be topic moved
                ->elm1('move', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $db, $permission)
                {
                    // Topic is not locked
                    if ($data->get('data.topic.topic_locked') == 0)
                    {
                        // Logged user has permission to manage topics in this forum
                        if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
                        {  
                            // If logged user has permission to move topics
                            if ($permission->has('topic.move'))
                            {
                                $forums = [];
                                foreach ($db->select('app.forum.all()') as $v)
                                {
                                    if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $v['permission_see']))
                                    {
                                        if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $v['permission_topic']))
                                        {
                                            if (!$v['forum_link'])
                                            {
                                                $forums[] = $v;
                                            }
                                        } 
                                    }
                                }

                                if (count($forums) > 1)
                                {
                                    $panel->fill(data: $forums, function: function ( \App\Visualization\Panel\Panel $panel ) use ($data)
                                    {
                                        if ($data->get('data.topic.forum_id') == $panel->get('data.forum_id'))
                                        {
                                            $panel->check();
                                        }
                                    })->show();
                                }
                            }
                        }
                    }
                });

            // Finish panel and get ready for generate
            $data->panel = $panel->getDataToGenerate();
        }

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_POSTS);
        $pagination->total($db->select('app.post.parentCount()', $this->url->getID(), $deleted));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Topic.json');
        $block
            // Setup topic
            ->elm1('topic', function ( \App\Visualization\Block\Block $block )
            {
                // If is this first page
                if (PAGE == 1)
                {
                    // Show topic
                    $block->show();
                }
            })
            // Fill topic with data
            ->elm1('topic')->appTo(data: $data->get('data.topic'), function: function ( \App\Visualization\Block\Block $block ) use ($user, $language)
            {
                // Default variables
                $block
                    // data.html.ajax-id = ID for ajax requests
                    ->set('data.html.ajax-id', $block->get('data.topic_id'))
                    // data.text = Text of topic
                    ->set('data.text', $block->get('data.topic_text'))
                    // data.name = Name of topic
                    ->set('data.name', $block->get('data.topic_name'))
                    // data.user = Link to user
                    ->set('data.user', $this->build->user->link(data: $block->get('data')))
                    // data.group = User group
                    ->set('data.group', $this->build->user->group(data: $block->get('data')))
                    // data.date = Date of topic creating
                    ->set('data.date', $this->build->date->long($block->get('data.topic_created'), true))
                    // data.user_image = User's profile image
                    ->set('data.user_image', $this->build->user->image(data: $block->get('data'), role: true, online: true, size: '50x50'))
                    // data.edited = Last time of editing
                    ->set('data.edited', $this->build->date->long($block->get('data.topic_edited_at')));

                // But if this topic wasn't edited yet
                if ($block->get('data.topic_edited') == 0)
                {
                    // Erase it
                    $block->set('data.edited', '');
                }

                // If user has any reputation
                if ($block->get('data.user_reputation'))
                {
                    // build reputation block
                    $block->set('data.reputation', $this->build->user->reputation($block->get('data.user_reputation')));
                }

                // If topic has header image
                if ($block->get('data.topic_image'))
                {
                    // Build path to image
                    $block->set('data.image_url', '/Uploads/Topics/' . $block->get('data.topic_id') . '/Header.' . $block->get('data.topic_image'));
                }

                // Foreach every like on topic
                foreach ($block->get('data.likes') as $key => $like)
                {
                    // If like is from logged user
                    if (LOGGED_USER_ID == $like['user_id'])
                    {
                        // Show "you" instead of username 
                        $block->set('data.likes.' . $key . '.user_name', '<span>' . $language->get('L_YOU') . '</span>');
                        continue;
                    }
                    // Build link to user
                    $block->set('data.likes.' . $key . '.user_name', $this->build->user->link(data: $like, group: false));
                }

                // If topic is deleted
                if ($block->get('data.deleted_id'))
                {
                    // delete all buttons
                    $block->delete('data.button');

                    // End
                    return;
                }

                // User is logged
                if ($user->isLogged())
                {
                    // Show 'report' button
                    $block->show('data.button.report');

                    // Topic is not from logged user
                    if (LOGGED_USER_ID != $block->get('data.user_id'))
                    {
                        // If user already liked this topic
                        if (in_array(LOGGED_USER_ID, array_column($block->get('data.likes'), 'user_id'))) 
                        {
                            // Show inlike button
                            $block->show('data.button.unlike');

                            // End
                            return;
                        }
                        
                        // Show like button
                        $block->show('data.button.like');
                    }
                }
            })
            ->elm1('post')
                // Setup block with input for creating new post
                ->elm2('bottom', function ( \App\Visualization\Block\Block $block ) use ($data, $permission)
                {
                    // Topic is not locked
                    if ($data->get('data.topic.topic_locked') == 0)
                    {
                        // Topic is not deleted
                        if (!$data->get('data.topic.deleted_id'))
                        {
                            // Logged user has permission to manage posts in this forum
                            if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_post')))
                            { 
                                // If user has permission to create posts
                                if ($permission->has('post.create'))
                                {
                                    // Show new post form
                                    $block->show();
                                }
                            }
                        }
                    }
                })
                // Set topic name and id to this form
                ->set('data.topic_name', $data->get('data.topic.topic_name'))
                ->set('data.html.ajax-id', $data->get('data.topic.topic_id'))
            // Setup posts
            ->elm1('post')->fill(data: $db->select('app.post.parent()', $this->url->getID(), $deleted), function: function ( \App\Visualization\Block\Block $block ) use ($language, $data,  $permission, $user)
			{
                // Default variables
                $block
                     // data.html.ajax-id = ID for ajax requests
                    ->set('data.html.ajax-id', $block->get('data.post_id'))
                    // data.text = Text of post
                    ->set('data.text', $block->get('data.post_text'))
                    // data.group = User's group
                    ->set('data.group', $this->build->user->group(data: $block->get('data')))
                    // data.date = Date of creating post
                    ->set('data.date', $this->build->date->long($block->get('data.post_created'), true))
                    // data.user = Link to user
                    ->set('data.user', $this->build->user->link(data: $block->get('data')))
                    // data.user_image = User's profile image
                    ->set('data.user_image', $this->build->user->image(data: $block->get('data'), role: true, online: true, size: '50x50'))
                    // data.name = Name of topic with Re
                    ->set('data.name', $language->get('L_RE') . ': ' . $block->get('data.topic_name'))
                    // data.edited = Date of last editing post
                    ->set('data.edited', $this->build->date->long($block->get('data.post_edited_at')));

                // But if this topic wasn't edited yet
                if ($block->get('data.post_edited') == 0)
                {
                    // Erase it
                    $block->set('data.edited', '');
                }

                // If user has any reputation
                if ($block->get('data.user_reputation'))
                {
                    // Show reputation
                    $block->set('data.reputation', $this->build->user->reputation($block->get('data.user_reputation')));
                }

                // Foreach every like on post
                foreach ($block->get('data.likes') as $key => $like)
                {
                    // If like is from logged user
                    if (LOGGED_USER_ID == $like['user_id'])
                    {
                        // Show "you" instead of username 
                        $block->set('data.likes.' . $key . '.user_name', $language->get('L_YOU'));
                        continue;
                    }
                    // Build link to user
                    $block->set('data.likes.' . $key . '.user_name', $this->build->user->link(data: $like, group: false));
                }

				// If topic is not deleted
				if ($data->get('data.topic.deleted_id') == null)
				{
                    // If post is not deleted
					if ($block->get('data.deleted_id') == null)
					{
                        // Topic is not locked
						if ($data->get('data.topic.topic_locked') == 0)
						{
                            // Logged user has permission to manage posts in this forum
							if (array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_post')))
							{
								// Logged user has permission to delete posts
								if ($permission->has('post.delete'))
								{
									$block->show('data.button.delete');
								}
                                
								// Logged user has permission to create posts
								if ($permission->has('post.create'))
								{
									$block->show('data.button.quote');
								}

								// Logged user has permission to edit own posts
								if ($permission->has('post.edit'))
								{
									// This post is not from logged user
									if (LOGGED_USER_ID == $block->get('data.user_id'))
									{
										// Show 'edit' button
										$block->show('data.button.edit');
									}
								}
							}
						}

						// User is logged
						if ($user->isLogged())
						{
							// Show 'report' button
							$block->show('data.button.report');

							// Post is not from logged user
							if (LOGGED_USER_ID != $block->get('data.user_id'))
							{
								// User already liked this post
								if (in_array(LOGGED_USER_ID, array_column($block->get('data.likes') ?: [], 'user_id'))) {
									$block->show('data.button.unlike');
								} else {
									$block->show('data.button.like');
								}
							}
						}
					}
				}

				// If post is deleted
				if ($block->get('data.deleted_id'))
				{
					// Show delete dnotice
					$block->notice('deleted');

					// Disable block
					$block->disable();

					// Close block
					$block->close();
				}

				// If is set 'select' parameter in url
				if ($this->url->is('select'))
				{
					// If this post is selected
					if ($block->get('data.post_id') == $this->url->get('select'))
					{
						// Select post
						$block->select();
					}
				}

				// Post is reported
				if ($block->get('data.report_id'))
				{
					// Report is not closed
					if ($block->get('data.report_status') == 0)
					{
						// Logged user has permisison to see reported content
						if ($permission->has('admin.forum'))
						{
							// Show notice
							$block->notice('reported');
						}
					}
				}
			});
	
        // Finish block and get ready from generate
        $data->block = $block->getDataToGenerate();

        // Update topic views
        $db->update(TABLE_TOPICS, [
            'topic_views' => [PLUS],
        ], $data->get('data.topic.topic_id'));
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
    public function newPost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if ($data->get('data.topic.topic_locked'))
        {
            return false;
        }

        if ($data->get('data.topic.deleted_id'))
        {
            return false;
        }

        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_post')))
        {
            return false;
        }

        // HTML Purifier
        $HTMLPurifier = new \App\Model\HTMLPurifier('big');

        // Inserts post to database
        $db->insert(TABLE_POSTS, [
            'topic_id'      => $data->get('data.topic.topic_id'),
            'post_text'     => $HTMLPurifier->purify($post->get('text')),
            'user_id'  		=> LOGGED_USER_ID,
            'forum_id'      => $data->get('data.topic.forum_id')
        ]);

        $ID = $db->lastInsertId();

        // Updates user number of posts
        $db->query('
            UPDATE ' . TABLE_USERS . '
            SET user_posts = user_posts + 1
            WHERE user_id = ?
        ', [LOGGED_USER_ID]);

        // Updates number of posts in topic and forum
        $db->query('
            UPDATE ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET topic_posts = topic_posts + 1,
                forum_posts = forum_posts + 1
            WHERE topic_id = ?
        ', [$data->get('data.topic.topic_id')]);

        // Send user notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $ID,
            to: $data->get('data.topic.user_id')
        );

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'post', id: $ID)
        ];
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
    public function markTopicWithLabels( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if ($post->get('labels'))
        {
            if (!is_array($post->get('labels')))
            {
                return false;
            }

            if (count($post->get('labels')) > 5)
            {
                throw new \App\Exception\Notice('topic_label_length_max');
            }
        }

        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
        {
            return false;
        }

        // Delete all labels from topic
        $db->query('
            DELETE tlb FROM ' . TABLE_TOPICS_LABELS . '
            WHERE topic_id = ?
        ', [$data->get('data.topic.topic_id')]);

        if ($post->get('labels'))
        {
            if (!is_array($post->get('labels')))
            {
                return false;
            }

            foreach ($post->get('labels') as $labelID)
            {
                // Insert new labels to topic
                $db->insert(TABLE_TOPICS_LABELS, [
                    'topic_id' => $data->get('data.topic.topic_id'),
                    'label_id' => $labelID
                ]);
            }
        }

        // Send notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $data->get('data.topic.topic_id'),
            to: $data->get('data.topic.user_id'),
            replace: true
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.topic.topic_name') );

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
    public function moveTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if ($data->get('data.topic.forum_id') == $post->get('forum_id'))
        {
            return false;
        }

        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
        {
            return false;
        }

        // Update statistics in old forum
        $db->query('
            UPDATE ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET f.forum_topics = f.forum_topics - 1,
                f.forum_posts = f.forum_posts - t.topic_posts
            WHERE t.topic_id = ?
        ', [$data->get('data.topic.topic_id')]);

        // Update statistics in new forum
        $db->query('
            UPDATE ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = ?
            SET f.forum_topics = f.forum_topics + 1,
                f.forum_posts = f.forum_posts + t.topic_posts,
                t.forum_id = ?,
                t.category_id = f.category_id
            WHERE t.topic_id = ?
        ', [$post->get('forum_id'), $post->get('forum_id'), $data->get('data.topic.topic_id')]);

        // Set new forum id to posts
        $db->query('
            UPDATE ' . TABLE_POSTS . '
            SET p.forum_id = ?
            WHERE p.topic_id = ?
        ', [$post->get('forum_id'), $data->get('data.topic.topic_id')]);

        // Send notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $data->get('data.topic.topic_id'),
            to: (int)$data->get('data.topic.user_id'),
            replace: true
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.topic.topic_name') );

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
    public function deleteTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if ($data->get('data.topic.deleted_id'))
        {
            return false;
        }

        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_topic')))
        {
            return false;
        }

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');
        
        $db->insert(TABLE_DELETED_CONTENT, [
            'user_id' => LOGGED_USER_ID,
            'deleted_type' => 'Topic',
            'deleted_type_id' => $data->get('data.topic.topic_id'),
            'deleted_type_user_id' => $data->get('data.topic.user_id')
        ]);

        $ID = $db->lastInsertID();
            
        // Set topic as deleted
        $db->query('
            UPDATE ' . TABLE_TOPICS . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET f.forum_posts = f.forum_posts - t.topic_posts,
            f.forum_topics = f.forum_topics - 1,
            t.deleted_id = ?
            WHERE t.topic_id = ?
        ', [$ID, $data->get('data.topic.topic_id')]);

        // Send notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $data->get('data.topic.topic_id'),
            to: $data->get('data.topic.user_id'),
            replace: false
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.topic.topic_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        if ($permission->has('admin.forum')) {
            
            // Refresh
            $data->set('options.refresh', true);

            return;
        }

        // Redirect
        $data->set('data.redirect', '/forum/show/' . $data->get('data.topic.forum_id') . '.' . $data->get('data.topic.forum_url') . '/');
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
    public function reportTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $reason = trim(strip_tags($post->get('report_reason_text')));
        if (!$reason)
        {
            throw new \App\Exception\Notice('report_reason_text');
        }

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        $ID = $data->get('data.topic.report_id');

        if (!$ID)
        {
            $db->insert(TABLE_REPORTS, [
                'report_type' => 'Topic',
                'report_type_id' => $data->get('data.topic.topic_id'),
                'report_type_user_id' => $data->get('data.topic.user_id')
            ]);
            $ID = $db->lastInsertId();

            $db->query('UPDATE ' . TABLE_TOPICS . ' SET report_id = ? WHERE t.topic_id = ?', [$ID, $data->get('data.topic.topic_id')]);
        }

        $db->query('
            UPDATE ' . TABLE_REPORTS . '
            SET report_status = 0
            WHERE report_id = ?
        ', [$ID]);

        $db->insert(TABLE_REPORTS_REASONS, [
            'user_id' => LOGGED_USER_ID,
            'report_id' => $ID,
            'report_reason_text' => $reason
        ]);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        if ($permission->has('admin.forum'))
        {
            $data->set('options.refresh', true);
        }
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
    public function likeTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if (in_array(LOGGED_USER_ID, array_column($data->get('data.topic.likes'), 'user_id')))
        {
            return false;
        }

        // Likes topic
        $db->insert(TABLE_TOPICS_LIKES, [
            'topic_id' => $data->get('data.topic.topic_id'),
            'user_id' => LOGGED_USER_ID
        ]);

        // Adds reputation
        $db->update(TABLE_USERS, [
            'user_reputation' => [PLUS]
        ], $data->get('data.topic.user_id'));

        // Send notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $data->get('data.topic.topic_id'),
            to: $data->get('data.topic.user_id'),
            replace: true
        );

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'topic', id: $post->get('id') )
        ];
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
    public function unlikeTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if (!in_array(LOGGED_USER_ID, array_column($data->get('data.topic.likes'), 'user_id')))
        {
            return false;
        }

        // Unlike topic
        $db->query('
            DELETE tl FROM ' . TABLE_TOPICS_LIKES . '
            WHERE topic_id = ? AND user_id = ?
        ', [$data->get('data.topic.topic_id'), LOGGED_USER_ID]);

        // Reduces user reputation
        $db->update(TABLE_USERS, [
            'user_reputation' => [MINUS]
        ], $data->get('data.topic.user_id'));

        // Delete old user notification
        $db->query('
            DELETE un FROM ' . TABLE_USERS_NOTIFICATIONS . '
            WHERE to_user_id = ? AND user_notification_item = "\Topic\Like" AND user_notification_item_id = ?
        ', [$data->get('data.topic.user_id'), $data->get('data.topic.topic_id')]);

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'topic', id: $post->get('id') )
        ];
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
    public function stickTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if ($data->get('data.topic.topic_sticked'))
        {
            return false;
        }

        // Stick topic
        $db->update(TABLE_TOPICS, [
            'topic_sticked' => '1'
        ], $data->get('data.topic.topic_id'));

        // Send notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $data->get('data.topic.topic_id'),
            to: $data->get('data.topic.user_id'),
            replace: true
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.topic.topic_name') );
        
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
    public function unstickTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if (!$data->get('data.topic.topic_sticked'))
        {
            return false;
        }

        // Unstick topic
        $db->update(TABLE_TOPICS, [
            'topic_sticked' => '0'
        ], $data->get('data.topic.topic_id'));

        // Send notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $data->get('data.topic.topic_id'),
            to: $data->get('data.topic.user_id'),
            replace: true
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.topic.topic_name') );
        
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
    public function lockTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if ($data->get('data.topic.topic_locked'))
        {
            return false;
        }

        $db->update(TABLE_TOPICS, ['topic_locked' => '1'], $data->get('data.topic.topic_id'));

        // Send notification to owner of topic
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $data->get('data.topic.topic_id'),
            to: $data->get('data.topic.user_id'),
            replace: true
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.topic.topic_name') );
        
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
    public function unlockTopic( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if (!$data->get('data.topic.topic_locked'))
        {
            return false;
        }

        // Unlock topic
        $db->update(TABLE_TOPICS, [
            'topic_locked' => '0'
        ], $data->get('data.topic.topic_id'));

        // Send notification to owner of topic
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $data->get('data.topic.topic_id'),
            to: $data->get('data.topic.user_id'),
            replace: true
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.topic.topic_name') );
        
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
    public function deletePost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get post data
        $row = $db->select('app.post.get()', $post->get('id'));
        if (!$row)
        {
            return false;
        }

        if ($row['deleted_id'])
        {
            return false;
        }

        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_post')))
        {
            return false;
        }

        // Add post to deleted content
        $db->insert(TABLE_DELETED_CONTENT, [
            'user_id' => LOGGED_USER_ID,
            'deleted_type' => 'Post',
            'deleted_type_id' => $post->get('id'),
            'deleted_type_user_id' => $_['user_id']
        ]);

        $db->query('
            UPDATE ' . TABLE_POSTS . '
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
            SET topic_posts = topic_posts - 1,
                forum_posts = forum_posts - 1,
                p.deleted_id = ?
            WHERE p.post_id = ?
        ', [$db->lastInsertID(), $post->get('id')]);

        // Send notifiation to owner of post
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $post->get('id'),
            to: $_['user_id']
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.topic.topic_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'post', id: $post->get('id') )
        ];
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
    public function reportPost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $reason = trim(strip_tags($post->get('report_reason_text')));
        if (!$reason)
        {
            throw new \App\Exception\Notice('report_reason_text');
        }

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Get post data
        $row = $db->select('app.post.get()', $post->get('id'));
        if (!$row)
        {
            return false;
        }

        $ID = $row['report_id'];

        if (!$ID)
        {
            $db->insert(TABLE_REPORTS, [
                'report_type' => 'Post',
                'report_type_id' => $post->get('id'),
                'report_type_user_id' => $row['user_id']
            ]);
            $ID = $db->lastInsertId();

            $db->query('UPDATE ' . TABLE_POSTS . ' SET report_id = ? WHERE p.post_id = ?', [$ID, $post->get('id')]);
        }

        $db->query('
            UPDATE ' . TABLE_REPORTS . '
            SET report_status = 0
            WHERE report_id = ?
        ', [$ID]);

        $db->insert(TABLE_REPORTS_REASONS, [
            'user_id' => LOGGED_USER_ID,
            'report_id' => $ID,
            'report_reason_text' => $reason
        ]);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        if ($permission->has('admin.forum'))
        {
            $content = new \App\Model\Content();

            return [
                'content' => $content->get( item: 'post', id: $post->get('id') )
            ];
        }
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
    public function likePost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get post data
        $row = $db->select('app.post.get()', $post->get('id'));
        if (!$row)
        {
            return false;
        }

        if (in_array(LOGGED_USER_ID, array_column($row['likes'], 'user_id')))
        {
            return false;
        }

        // Likes post
        $db->insert(TABLE_POSTS_LIKES, [
            'post_id' => $post->get('id'),
            'user_id' => LOGGED_USER_ID
        ]);

        // Adds reputation
        $db->update(TABLE_USERS, [
            'user_reputation' => [PLUS],
        ], $_['user_id']);

        // Send user notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $post->get('id'),
            to: $_['user_id'],
            replace: true
        );

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'post', id: $post->get('id') )
        ];
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
    public function unlikePost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get post data
        $row = $db->select('app.post.get()', $post->get('id'));
        if (!$row)
        {
            return false;
        }

        if (!in_array(LOGGED_USER_ID, array_column($row['likes'], 'user_id')))
        {
            return false;
        }

        // Unlike post
        $db->query('
            DELETE pl FROM ' . TABLE_POSTS_LIKES . '
            WHERE post_id = ? AND user_id = ?
        ', [$post->get('id'), LOGGED_USER_ID]);

        // Reduces user reputation
        $db->update(TABLE_USERS, [
            'user_reputation' => [MINUS]
        ], $_['user_id']);

        // Delete old user notification
        $db->query('
            DELETE un FROM ' . TABLE_USERS_NOTIFICATIONS . '
            WHERE to_user_id = ? AND user_notification_item = "\Post\Like" AND user_notification_item_id = ?
        ', [$_['user_id'], $post->get('id')]);

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'post', id: $post->get('id') )
        ];
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
    public function editorPost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Content
        $content = new \App\Model\Content();
        
        return [
            'trumbowyg' => $data->get('data.trumbowyg.big'),
            'button' => $content->get(
                url: 'Root/Style:/Templates/Blocks/Visualization/Block/Buttons/Save.phtml'
            )
        ];
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
    public function editPost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get profile post
        $row = $db->select('app.post.get()', $post->get('id'));
        if (!$row)
        {
            return false;
        }

        if ($row['user_id'] != LOGGED_USER_ID)
        {
            return false;
        }

        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $data->get('data.topic.permission_post')))
        {
            return false;
        }

        // HTML Purifier
        $HTMLPurifier = new \App\Model\HTMLPurifier('big');

        // Edits post
        $db->update(TABLE_POSTS, [
            'post_text'         => $HTMLPurifier->purify($post->get('text')),
            'post_edited'       => '1',
            'post_edited_at'    => DATE_DATABASE
        ], $post->get('id'));

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'post', id: $post->get('id') )
        ];
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
    public function windowPostLikes( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get post data
        $likes = $db->select('app.post.likes()', $post->get('id'));

        if (!$likes)
        {
            return false;
        }

        $content = '';
        foreach ($likes as $user)
        {
            $content .= $this->build->user->linkImg( data: $user, group: true );
        }

        // Language
        $language = $data->get('inst.language');

        return [
            'title' => $language->get('L_WINDOW.L_TITLE.L_LIKES'),
            'content' => $content
        ];
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
    public function windowTopicLikes( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get post data
        $likes = $db->select('app.topic.likes()', $post->get('id'));

        if (!$likes)
        {
            return false;
        }

        $content = '';
        foreach ($likes as $user)
        {
            $content .= $this->build->user->linkImg( data: $user, group: true );
        }

        // Language
        $language = $data->get('inst.language');

        return [
            'title' => $language->get('L_WINDOW.L_TITLE.L_LIKES'),
            'content' => $content
        ];
    }
}