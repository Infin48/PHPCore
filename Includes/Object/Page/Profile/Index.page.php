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

namespace App\Page\Profile;

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
    protected bool $editor = true;

    /**
     * @var string $template Page template
     */
    protected string $template = '/Includes/Template/Profile.phtml';

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

        // If profiles are disabled
        if ($system->get('site.mode.blog.profiles') == 0)
        {
            // Show 404 error page
            $this->error404();
        }

        // User data
        $row = $db->select('app.user.get()', $this->url->getID()) or $this->error404();

        // Save user data
        $data->set('data.profile', $row);

        // Show profile header
        $data->set('options.profileHeader', true);
        $data->set('data.profile.roles', $db->select('app.role.parent()', $data->get('data.profile.user_roles')));

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Users.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.profile.user_name'))->href($this->build->url->profile($data->get('data.profile')));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        $data->set('data.profile.online', false);
        if (time() - strtotime($data->get('data.profile.user_last_activity')) <= 60)
        {
            $data->set('data.profile.online', true);
        }
        
        // Sidebar
        $sidebar = new \App\Visualization\Sidebar\Sidebar('Root/Sidebar:/Formats/Profile.json');
        $sidebar->left();
        
        // Last logged in
        $sidebar->elm1('user')->elm2('online')->elm3('online', function ( \App\Visualization\Sidebar\Sidebar $sidebar ) use ($data)
        {
            if (!$data->get('data.profile.online'))
            {
                $sidebar->value($this->build->date->short($data->get('data.profile.user_last_activity')))->up()->show();
            }
        });

        
        if ($system->get('site.mode') != 'blog')
        {
            $sidebar->elm1('user')->elm2('topics')->show()->elm2('posts')->show();
        } else {
            $sidebar->elm1('user')->elm2('articles')->show();
        }


        // Contact object
        $sidebar->elm1('contact', function ( \App\Visualization\Sidebar\Sidebar $sidebar ) use ($data, $system, $user)
        {
            // Setup "send pm" button
            // If os disabled blog mode
            if ($system->get('site.mode') != 'blog')
            {
                // If user is logged
                if ($user->isLogged())
                {
                    // If logged user is not looking at own profile
                    if ($data->get('data.profile.user_id') != LOGGED_USER_ID)
                    {
                        // Show button and setup link
                        $sidebar->show()->elm2('send_pm')->show()->set('data.href', '/user/conversation/add/to-' . $data->get('data.profile.user_id'));
                    }
                }
            }

            // If user has filled any contact form
            if ($data->get('data.profile.user_discord') or $data->get('data.profile.user_instagram') or $data->get('data.profile.user_facebook'))
            {
                // Show object
                $sidebar->show();

                // If user has discord
                if ($data->get('data.profile.user_discord'))
                {
                    $sidebar->elm2('user_discord')->show()->elm3('user_discord')->value($data->get('data.profile.user_discord'));
                }

                // If user has instagram
                if ($data->get('data.profile.user_instagram'))
                {
                    $sidebar->elm2('user_instagram')->show()->set('data.href', $data->get('data.profile.user_instagram'));
                }

                // If user has facebook
                if ($data->get('data.profile.user_facebook'))
                {
                    $sidebar->elm2('user_facebook')->show()->set('data.href', $data->get('data.profile.user_facebook'));
                }

                return;
            }
        });

        // Main object of sidebar
        $sidebar->elm1('user', function ( \App\Visualization\Sidebar\Sidebar $sidebar ) use ($data, $user, $permission)
        {
            $sidebar->elm2('image')->set('data.user_image', $this->build->user->image(data: $data->get('data.profile'), size: '150x150'));
            // Set values to rows
            $sidebar
                ->elm2('registered')->elm3('registered')->value($this->build->date->short($data->get('data.profile.user_registered')))
                ->elm2('topics')->elm3('topics')->value($data->get('data.profile.user_topics'))
                ->elm2('posts')->elm3('posts')->value($data->get('data.profile.user_posts'))
                ->elm2('articles')->elm3('articles')->value($data->get('data.profile.user_articles'));

            // Setup edit button
            // If user is logged
            if ($user->isLogged())
            {  
                // If group from logged user has higher index
                if (LOGGED_USER_GROUP_INDEX > $data->get('group_index'))
                {
                    // And if logged user has permission to edit other users
                    if ($permission->has('admin.user'))
                    {
                        // Show button and setup link
                        $sidebar->elm2('edit_user')->show()->set('data.href', '/admin/user/show/' . $data->get('data.profile.user_id'));
                    }
                }
            }
        });
        
        // Info object
        $sidebar->elm1('info', function ( \App\Visualization\Sidebar\Sidebar $sidebar ) use ($data)
        {
            // User gender
            if ($data->get('data.profile.user_gender') != 'undefined')
            {
                $sidebar->show()->elm2('user_gender')->show()->elm3('user_gender')->value('L_' . strtoupper($data->get('data.profile.user_gender')));
            }

            // User location
            if ($data->get('data.profile.user_location'))
            {
                $sidebar->show()->elm2('user_location')->show()->elm3('user_location')->value($data->get('data.profile.user_location'));
            }

            // User age
            if ($data->get('data.profile.user_age'))
            {
                $sidebar->show()->elm2('user_age')->show()->elm3('user_age')->value($data->get('data.profile.user_age'));
            }
        });
        $data->sidebar = $sidebar->getDataToGenerate();

        $page = match (TAB)
        {
            'signature' => '\App\Page\Profile\Tab\Signature',
            'activity' => '\App\Page\Profile\Tab\Activity',
            'about' => '\App\Page\Profile\Tab\About',
            default => '\App\Page\Profile\Tab\ProfilePost'
        };

        if ($system->get('site.mode') == 'blog')
        {
            $page = '\App\Page\Profile\Tab\About';
        }

        $page = $this->buildPage( class: $page );
        $page->body( $data, $db );

        $page->checkForAjax();

        // Set head of this page
        $data->set('data.head.title', $data->get('data.profile.user_name'));
    }
}