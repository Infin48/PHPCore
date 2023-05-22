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

namespace App\Page\Profile\Tab;

/**
 * ProfilePost
 */
class ProfilePost extends \App\Page\Page
{
    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Profile/ProfilePost.phtml';

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
            'run/profile-post/editor',
            'run/profile-post-comment/editor' => 'showEditor',

            'run/profile-post/edit' => 'editProfilePost',
            'run/profile-post/create' => 'newProfilePost',
            'run/profile-post/delete' => 'deleteProfilePost',
            'run/profile-post/report' => 'reportProfilePost',
            'run/profile-post/previous' => 'previousProfilePostComment',
            
            'run/profile-post-comment/edit' => 'editProfilePostComment',
            'run/profile-post-comment/create' => 'newProfilePostComment',
            'run/profile-post-comment/delete' => 'deleteProfilePostComment',
            'run/profile-post-comment/report' => 'reportProfilePostComment',

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
            'run/profile-post/delete',
            'run/profile-post-comment/delete',
            'run/profile-post/previous' => [
                'id' => STRING
            ],

            'run/profile-post/edit',
            'run/profile-post/create',
            'run/profile-post-comment/edit',
            'run/profile-post-comment/create' => [
                'id' => STRING,
                'text' => STRING
            ],

            'run/profile-post/report',
            'run/profile-post-comment/report' => [
                'id' => STRING,
                'report_reason_text' => STRING
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
            'run/profile-post/edit',
            'run/profile-post/editor',
            'run/profile-post-comment/edit',
            'run/profile-post-comment/editor' => 'profilepost.edit',

            'run/profile-post/create',
            'run/profile-post-comment/create' => 'profilepost.create',

            'run/profile-post/delete',
            'run/profile-post-comment/delete' => 'profilepost.delete',

            'run/profile-post/report',
            'run/profile-post-comment/report' => true,

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
        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');
        
        // If logged user has permission to see deleted content
        $deleted = false;
        if ($permission->has('admin.forum'))
        {
            $deleted = true;
        }

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_PROFILE_POSTS);
        $pagination->url($this->url->getURL());
        $pagination->total($db->select('app.profile-post.parentCount()', $this->url->getID(), $deleted));
        $data->pagination = $pagination->getData();
        
        $selectedCommentPosition = 0;
        if ($this->url->get('select.c'))
        {
            $_ = $db->select('app.profile-post-comment.get()', $this->url->get('select.c'), $deleted);
            
            if ($_)
            {
                if ($_['profile_id'] == $this->url->getID())
                {
                    $selectedCommentPosition = $_['profile_post_comment_position'];
                }
            }
        }

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Profile/ProfilePost.json');
        $block->elm1('new', function ( \App\Visualization\Block\Block $block ) use ($user, $permission)
        {
            // Logged user has permission to create new porfile posts
           if ($permission->has('profilepost.create'))
           {
               $block->show();
           } 
        });

        $block->elm1('profilepost')->fill(data: $db->select('app.profile-post.parent()', $this->url->getID(), $deleted), function: function ( \App\Visualization\Block\Block $block ) use ($permission, $user, $db, $deleted, $selectedCommentPosition)
        {
            // Define variables
            $block
                // data.html.ajax-id = ID for ajax requests
                ->set('data.html.ajax-id', $block->get('data.profile_post_id'))
                // data.date = Date of creating profile post
                ->set('data.date', $this->build->date->short($block->get('data.profile_post_created')))
                // data.text = Text of profile post
                ->set('data.text', $block->get('data.profile_post_text'), 400)
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $block->get('data')))
                // date.user_image = User's profile image
                ->set('data.user_image', $this->build->user->image(data: $block->get('data'), online: true, role: true, size: '25x25'));

            // If profile post is not deleted
            if (!$block->get('data.deleted_id'))
            {
                // Logged user has permisison to create new profile posts
                if ($permission->has('profilepost.create'))
                {
                    $id = $block->get('data.profile_post_id');
                    $block->elm3('bottom')->show()->set('data.profile_post_id', $id)->up();
                }

                // Logged user has permission to delete profile posts
                if ($permission->has('profilepost.delete'))
                {
                    $block->show('data.button.delete');
                }

                // Logged user has permission to edit own profile posts
                if ($permission->has('profilepost.edit'))
                {
                    // This post is not from logged user
                    if (LOGGED_USER_ID == $block->get('data.user_id'))
                    {
                        // Show 'edit' button
                        $block->show('data.button.edit');
                    }
                }

                // User is logged
                if ($user->isLogged())
                {
                    // Show 'report' button
                    $block->show('data.button.report');
                }
            }

            // If profile post is deleted
            if ($block->get('data.deleted_id'))
            {
                // Show notice
                $block->notice('deleted');

                // Disable block
                $block->disable();

                // Close block
                $block->close();
            }

            // Profile post is reported
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
            
            // If comments is more than 5
            if ($block->get('data.next') == 1)
            {
                // Show previous comments
                $id = $block->get('data.profile_post_id');
                $block->elm3('top')->show()->set('data.profile_post_id', $id)->up();
            }

            // If is this profile psot selected throught URL
            if ($this->url->get('select') == $block->get('data.profile_post_id'))
            {
                $block
                    // Seletc this row(post) 
                    ->select()
                    // And open it, if is closed
                    ->open();
            }

            if ($this->url->get('select.p') == $block->get('data.profile_post_id'))
            {
                $block->open();
                if ($selectedCommentPosition > 5)
                {
                    $comments = $db->select('app.profile-post-comment.parent()', $block->get('data.profile_post_id'), null, $deleted);
                    $block->open()->elm3('top')->hide()->up();
                }
            }

            if (!isset($comments))
            {
                $comments = $db->select('app.profile-post-comment.parent()', $block->get('data.profile_post_id'), 5, $deleted);
            }
            $post = $block->get('data');

            $block->set('body.bottom.data.profile_post_id', $block->get('data.profile_post_id'));

            $block->fill(data: $comments, function: function ( \App\Visualization\Block\Block $block ) use ($post, $user, $permission)
            {
                // Define variables
                $block
                    // data.html.ajax-id = ID for ajax requests
                    ->set('data.html.ajax-id', $block->get('data.profile_post_comment_id'))
                    // data.date = Date of creating comment
                    ->set('data.date', $this->build->date->short($block->get('data.profile_post_comment_created')))
                    // data.text = Text of comment
                    ->set('data.text', $block->get('data.profile_post_comment_text'), 400)
                    // data.user = Link to user
                    ->set('data.user', $this->build->user->link(data: $block->get('data')))
                    // date.user_image = User's profile image
                    ->set('data.user_image', $this->build->user->image(data: $block->get('data'), online: true, role: true, size: '25x25'));

                // If profile comment is not deleted
                if (!$block->get('data.deleted_id'))
                {
                    // If profile post is not deleted
                    if (!$post['deleted_id'])
                    {
                        // Logged user has permission to delete profile posts
                        if ($permission->has('profilepost.delete'))
                        {
                            // Show delete button
                            $block->show('data.button.delete');
                        }

                        // Logged user has permission to edit own profile posts
                        if ($permission->has('profilepost.edit'))
                        {
                            // This post is not from logged user
                            if (LOGGED_USER_ID == $block->get('data.user_id'))
                            {
                                // Show 'edit' button
                                $block->show('data.button.edit');
                            }
                        }

                        // User is logged
                        if ($user->isLogged())
                        {
                            // Show 'report' button
                            $block->show('data.button.report');
                        }
                    }
                }

                // If profile comment is deleted
                if ($block->get('data.deleted_id'))
                {
                    // Show notice
                    $block->notice('deleted');

                    // Disable block
                    $block->disable();

                    // Close block
                    $block->close();
                }

                // Profile comment is reported
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

                // If is this comment selected throught URL
                if ($this->url->get('select.c') == $block->get('data.profile_post_comment_id'))
                {
                    $block
                        // Select this row(comment)
                        ->select()
                        // And open it, if is closed
                        ->open();
                }
            });
        });

        // Finish block and get ready to generate
        $data->block = $block->getDataToGenerate();
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
    public function newProfilePost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // HTML Purifier
        $HTMLPurifier = new \App\Model\HTMLPurifier('big');
        
        $db->insert(TABLE_PROFILE_POSTS, [
            'user_id'           => LOGGED_USER_ID,
            'profile_id'        => $data->get('data.profile.user_id'),
            'profile_post_text' => $HTMLPurifier->purify($post->get('text'))
        ]);

        $id = $db->lastInsertId();

        // Send user notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $id,
            to: $data->get('data.profile.user_id')
        );

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post', id: $id ),
            'form' => $content->get( url: '/Includes/Object/Visualization/Block/Templates/ProfilePostNew.phtml', data: ['profile' => ['user_id' => $data->get('data.profile.user_id')]] )
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
    public function deleteProfilePost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get profile post
        $_ = $db->select('app.profile-post.get()', $post->get('id'));
        if (!$_)
        {
            return false;
        }

        $db->insert(TABLE_DELETED_CONTENT, [
            'user_id' => LOGGED_USER_ID,
            'deleted_type' => 'ProfilePost',
            'deleted_type_id' => $post->get('id'),
            'deleted_type_user_id' => $_['user_id']
        ]);

        // Set deleted id to profile post
        $db->update(TABLE_PROFILE_POSTS, [
            'deleted_id' => $db->lastInsertID()
        ], $post->get('id'));

        // Send user notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $post->get('id'),
            to: $_['user_id']
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post', id: $post->get('id') )
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
    public function reportProfilePost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $reason = trim(strip_tags($post->get('report_reason_text')));
        if (!$reason)
        {
            throw new \App\Exception\Notice('report_reason_text');
        }

        // Get profile post
        $_ = $db->select('app.profile-post.get()', $post->get('id'));
        if (!$_)
        {
            return false;
        }

        $ID = $_['report_id'];

        if (!$ID)
        {
            $db->insert(TABLE_REPORTS, [
                'report_type' => 'ProfilePost',
                'report_type_id' => $post->get('id'),
                'report_type_user_id' => $_['user_id']
            ]);
            $ID = $db->lastInsertId();

            $db->query('UPDATE ' . TABLE_PROFILE_POSTS . ' SET report_id = ? WHERE pp.profile_post_id = ?', [$ID, $post->get('id')]);
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

        // Permission
        $permission = $data->get('inst.user')->get('permission');

        if (!$permission->has('admin.forum'))
        {
            return;
        }

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post', id: $post->get('id') )
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
    public function newProfilePostComment( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get profile post
        $_ = $db->select('app.profile-post.get()', $post->get('id'));
        if (!$_)
        {
            return false;
        }

        // HTML Purifier
        $HTMLPurifier = new \App\Model\HTMLPurifier('big');

        // Creates new profile sub post
        $db->insert(TABLE_PROFILE_POSTS_COMMENTS, [
            'user_id'               => LOGGED_USER_ID,
            'profile_id'            => $data->get('data.profile.user_id'),
            'profile_post_id'       => $post->get('id'),
            'profile_post_comment_text' => $HTMLPurifier->purify($post->get('text'))
        ]);

        $ID = $db->lastInsertId();

        // Send user notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $db->lastInsertId(),
            to: $_['user_id']
        );

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post-comment', id: $ID ),
            'form' => $content->get( url: '/Includes/Object/Visualization/Block/Templates/ProfilePostCommentNew.phtml', data: [
                'profile_post_id' => $post->get('id')
            ])
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
    public function deleteProfilePostComment( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $comment = $db->select('app.profile-post-comment.get()', $post->get('id'));
        if (!$comment)
        {
            return false;
        }

        $db->insert(TABLE_DELETED_CONTENT, [
            'user_id' => LOGGED_USER_ID,
            'deleted_type' => 'ProfilePostComment',
            'deleted_type_id' => $post->get('id'),
            'deleted_type_user_id' => $comment['user_id']
        ]);

        $db->query('
            UPDATE ' . TABLE_PROFILE_POSTS_COMMENTS . ' SET deleted_id = ? WHERE profile_post_comment_id = ?
        ', [$db->lastInsertID(), $post->get('id')]);

        // Send user notification
        $db->sendNotification(
            name: __FUNCTION__,
            ID: $post->get('id'),
            to: $comment['user_id']
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post-comment', id: $post->get('id') )
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
    public function reportProfilePostComment( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $reason = trim(strip_tags($post->get('report_reason_text')));
        if (!$reason)
        {
            throw new \App\Exception\Notice('report_reason_text');
        }

        // Get post data
        $comment = $db->select('app.profile-post-comment.get()', $post->get('id'));
        if (!$comment)
        {
            return false;
        }

        $ID = $comment['report_id'];

        if (!$ID)
        {
            $db->insert(TABLE_REPORTS, [
                'report_type' => 'ProfilePostComment',
                'report_type_id' => $post->get('id'),
                'report_type_user_id' => $comment['user_id']
            ]);
            $ID = $db->lastInsertId();

            $db->query('UPDATE ' . TABLE_PROFILE_POSTS_COMMENTS . ' SET report_id = ? WHERE ppc.profile_post_comment_id = ?', [$ID, $post->get('id')]);
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

        // User
        $user = $data->get('inst.user');

        // Permission
        $permission = $user->get('permission');

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        if (!$permission->has('admin.forum'))
        {
            return;
        }

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post-comment', id: $post->get('id') )
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
    public function previousProfilePostComment( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post', id: $post->get('id'), data: ['comments' => 'all'])
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
    public function showEditor( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Content
        $content = new \App\Model\Content();
        
        return [
            'trumbowyg' => $data->get('data.trumbowyg.small'),
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
    public function editProfilePost( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get profile post
        $_ = $db->select('app.profile-post.get()', $post->get('id'));
        if (!$_)
        {
            return false;
        }

        if ($_['user_id'] != LOGGED_USER_ID)
        {
            return false;
        }

        // HTML Purifier
        $HTMLPurifier = new \App\Model\HTMLPurifier('big');

        // Update profile post
        $db->update(TABLE_PROFILE_POSTS, [
            'profile_post_text' => $HTMLPurifier->purify($post->get('text'))
        ], $post->get('id'));

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post', id: $post->get('id') )
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
    public function editProfilePostComment( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get post data
        $comment = $db->select('app.profile-post-comment.get()', $post->get('id'));
        if (!$comment)
        {
            return false;
        }

        if ($comment['user_id'] != LOGGED_USER_ID)
        {
            return false;
        }

        // HTML Purifier
        $HTMLPurifier = new \App\Model\HTMLPurifier('big');

        // Update profile sub post
        $db->update(TABLE_PROFILE_POSTS_COMMENTS, [
            'profile_post_comment_text' => $HTMLPurifier->purify($post->get('text'))
        ], $post->get('id'));

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( item: 'profile-post-comment', id: $post->get('id') )
        ];
    }
}