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

namespace App\Page\Admin\User;

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
    protected string $permission = 'admin.user';

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
            'run/user/delete' => 'deleteUser',
            'run/user/promote' => 'promoteUser',
            'run/user/activate' => 'activateUser',

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

        // If static mode is enabled
		if ($system->get('site_mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('users')->elm2('user')->active();

        // Load user data from database
        $row = $db->select('app.user.get()', $this->url->getID()) or $this->error404();

        // Save user data and unite with others
        $data->set('data.profile', $row);
        $data->set('data.profile.user_roles', explode(',', $data->get('data.profile.user_roles')));

        if (LOGGED_USER_ID != $data->get('data.profile.user_id'))
        {
            if (LOGGED_USER_GROUP_ID != 1)
            {
                if ($data->get('data.profile.group_index') >= LOGGED_USER_GROUP_INDEX)
                {
                    $this->error404();
                }
            }
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/User.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.profile.user_name'))->href('/admin/user/show/' . $data->get('data.profile.user_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();
        
        $roles = [];
        if ($permission->has('admin.role'))
        {
            // Roles
            $roles = $db->select('app.role.all()');
        }

        // Groups
        $groups = [['group_id' => 1]];
        if ($data->get('data.profile.group_id') != 1)
        {
            $groups = $db->select('app.group.less()');
        }

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/User/User.json');
        $form
            ->form('user')
                ->callOnSuccess($this, 'editUser')
                ->data($data->get('data.profile'))
                ->frame('user')
                    ->id($data->get('data.profile.user_id'))
                    ->delete('data.title')
                    ->input('show', function ( \App\Visualization\Form\Form $form ) use ($groups, $data, $system)
                    {
                        if ($system->get('site_mode_blog_profiles') != 0)
                        {
                            $form->show()->set('data.href', $this->build->url->profile($data->get('data.profile')));
                        }
                    })

                    ->input('group_id', function ( \App\Visualization\Form\Form $form ) use ($groups, $data)
                    {
                        // Loggeduser doesnt't edit himself
                        if (LOGGED_USER_ID != $data->get('data.profile.user_id'))
                        {
                            $form->show()->fill($groups);
                        }
                    })

                    ->input('user_roles', function ( \App\Visualization\Form\Form $form ) use ($roles, $system, $data)
                    {
                        // If profiles are enabled
                        if ($system->get('site_mode_blog_profiles'))
                        {
                            $form->show()->fill( data: $roles );
                        }
                    })

                    // If user doesn't have activated account
                    ->input('activate', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.profile.account_code'))
                        {
                            $form->show();
                        }
                    })

                    // If logged user is admin
                    ->input('promote', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if (LOGGED_USER_GROUP_ID == 1)
                        {
                            if ($data->get('data.profile.group_id') != 1)
                            {
                                $form->show();
                            }
                        }
                    })

                    // If logged user is current edited user
                    ->input('delete', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if (LOGGED_USER_ID != $data->get('data.profile.user_id'))
                        {
                            $form->show();
                        }
                    })

                    // Show delete signature
                    ->input('delete_signature', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.profile.user_signature'))
                        {
                            $form->show();
                        }
                    })

                    // Show delete signature
                    ->input('delete_about', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.profile.user_about'))
                        {
                            $form->show();
                        }
                    })

                    // Show delete user text
                    ->input('delete_user_text', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.profile.user_text'))
                        {
                            $form->show();
                        }
                    })

                    // Show delete profile image
                    ->input('delete_profile_image', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if (!in_array($data->get('data.profile.user_profile_image'), PROFILE_IMAGES_COLORS))
                        {
                            $form->show();
                        }
                    })

                    // Show delete header image
                    ->input('delete_header_image', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        if ($data->get('data.profile.user_header_image'))
                        {
                            $form->show();
                        }
                    });
        $data->form = $form->getDataToGenerate();

        // Page title
        $data->set('data.head.title', $language->get('L_USER.L_USER') . ' - ' . $data->get('data.profile.user_name'));
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
    public function editUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // If exists user with entered user name
        if ($db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_name = ? and user_id <> ?', [$post->get('user_name'), $data->get('data.profile.user_id')]))
        {
            throw new \App\Exception\Notice('user_name_exist');
        }

        // If exists user with entered e-mail
        if ($db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_email = ? and user_id <> ?', [$post->get('user_email'), $data->get('data.profile.user_id')]))
        {
            throw new \App\Exception\Notice('user_email_exist');
        }

        $check = new \App\Model\Check();

        // If is entered pasword
        if ($post->get('user_password'))
        {
            // Check password
            if ($check->password($post->get('user_password')))
            {
                // Change user password
                $db->update(TABLE_USERS, [
                    'user_password' => password_hash($post->get('user_password'), PASSWORD_DEFAULT)
                ], $data->get('data.profile.user_id'));
            }
        }

        // Check e-mail
        if (!$check->email($post->get('user_email')))
        {
            return false;
        }

        // If was checked "delete signature"
        if ($post->get('delete_signature'))
        {
            // Delete user signature
            $db->update(TABLE_USERS, [
                'user_signature' => ''
            ], $data->get('data.profile.user_id'));
        }

        // File model
        $file = new \App\Model\File\File();

        if ($post->get('delete_profile_image'))
        {
            // Delete user profile image
            $db->update(TABLE_USERS, [
                'user_profile_image' => getProfileImageColor()
            ], $data->get('data.profile.user_id'));

            // Delete profile image from server
            $file->delete('/Uplaods/Users/' . $data->get('data.profile.user_id') . '/Profile.*');
        }

        if ($post->get('delete_header_image'))
        {
            // Delete user header image
            $db->update(TABLE_USERS, [
                'user_header_image' => ''
            ], $data->get('data.profile.user_id'));

            // Delete header image from server
            $file->delete('/Uploads/Users/' . $data->get('data.profile.user_id') . '/Header.*');
        }

        if ($post->get('delete_user_text'))
        {
            // Delete user header image
            $db->update(TABLE_USERS, [
                'user_text' => ''
            ], $data->get('data.profile.user_id'));
        }

        if ($post->get('delete_about'))
        {
            // Delete user header image
            $db->update(TABLE_USERS, [
                'user_about' => ''
            ], $data->get('data.profile.user_id'));
        }

        // Delete email verification to enetered email
        $db->delete(
            table: TABLE_VERIFY_EMAIL,
            key: 'user_email',
            id: $post->get('user_email')
        );

        if ($post->get('group_id'))
        {
            // Edit user group
            $db->update(TABLE_USERS, [
                'group_id'          => $post->get('group_id'),
            ], $data->get('data.profile.user_id'));
        }

        // Edit user
        $db->update(TABLE_USERS, [
            'user_name'         => $post->get('user_name'),
            'user_email'        => $post->get('user_email'),
            'user_roles'        => implode(',', $post->get('user_roles'))
        ], $data->get('data.profile.user_id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('user_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
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
    public function deleteUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // System
        $system = $data->get('inst.system');

        // Select user as deleted
        $db->query('
            UPDATE ' . TABLE_USERS . '
            SET user_deleted = 1,
                user_profile_image = "",
                user_header_image = "",
                user_reputation = 0,
                user_signature = "",
                user_password = "",
                user_location = "",
                user_gender = "",
                user_topics = 0,
                user_posts = 0,
                user_email = "",
                user_text = "",
                user_name = "",
                user_hash = "",
                group_id = ?,
                user_age = 0
            WHERE user_id = ?
        ', [$system->get('default_group'), $data->get('data.profile.user_id')]);

        // Delete user's content
        $db->query('
            DELETE pl, tl, unr, un, pp, dc, r, ppc, dc2, r2, fp, va, ve, cr
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_POSTS_LIKES . ' ON pl.user_id = u.user_id
            LEFT JOIN ' . TABLE_TOPICS_LIKES . ' ON tl.user_id = u.user_id
            LEFT JOIN ' . TABLE_USERS_UNREAD . ' ON unr.user_id = u.user_id
            LEFT JOIN ' . TABLE_USERS_NOTIFICATIONS . ' ON to_user_id = u.user_id
            LEFT JOIN ' . TABLE_PROFILE_POSTS . ' ON pp.profile_id = u.user_id
            LEFT JOIN ' . TABLE_DELETED_CONTENT . ' ON dc.deleted_id = pp.deleted_id
            LEFT JOIN ' . TABLE_REPORTS . ' ON r.report_id = pp.report_id
            LEFT JOIN ' . TABLE_PROFILE_POSTS_COMMENTS . ' ON ppc.profile_post_id = pp.profile_post_id
            LEFT JOIN ' . TABLE_DELETED_CONTENT . '2 ON dc.deleted_id = ppc.deleted_id
            LEFT JOIN ' . TABLE_REPORTS . '2 ON r.report_id = ppc.report_id
            LEFT JOIN ' . TABLE_FORGOT . ' ON fp.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_ACCOUNT . ' ON va.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_EMAIL . ' ON ve.user_id = u.user_id
            LEFT JOIN ' . TABLE_CONVERSATIONS_RECIPIENTS . ' ON cr.user_id = u.user_id
            WHERE u.user_id = ?
        ', [$data->get('data.profile.user_id')]);

        // File model
        $file = new \App\Model\File\File();

        // Delete profile image
        $file->delete('/Uploads/Users/' . $data->get('data.profile.user_id') . '/Profile.*');

        // Delete header image
        $file->delete('/Uploads/Users/' . $data->get('data.profile.user_id') . '/Header.*');

        // Increment deleted users by 1
        $db->stats([
            'user_deleted' =>  + 1
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.profile.user_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect back
        $data->set('data.redirect', '/admin/user/');
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
    public function promoteUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // System
        $system = $data->get('inst.system');

        // Set user as main administrator
        $db->query('
            UPDATE ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.group_id = 1
            SET u.group_id = 1,
                u2.group_id = ?
            WHERE u.user_id = ?
        ', [$system->get('default_group'), $data->get('data.profile.user_id')]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.profile.user_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect back
        $data->set('data.redirect', INDEX);
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
    public function activateUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Delete record about unactivated account
        $db->delete(
            table: TABLE_VERIFY_ACCOUNT,
            id: $data->get('data.profile.user_id')
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.profile.user_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Refrsh page
        $data->set('options.refresh', true);
    }
}