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

namespace App\Page;

/**
 * Router
 */
class Router extends Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Body.phtml';

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
            'window/delete-attachment', 'window/article/delete', 'window/topic/delete', 'window/post/delete', 'window/profile-post/delete', 'window/profile-post-comment/delete', 'window/message/delete' => 'windowDelete',
            'window/post/report', 'window/profile-post/report', 'window/profile-post-comment/report', 'window/topic/report' => 'windowReport',

            'run/logout' => 'logoutUser',
            'run/mention-user' => 'mentionUser',
            'run/close-preview' => 'closePreview',
            'run/delete-attachment' => 'deleteAttachment',
            'run/mark-user-notifications-as-read' => 'markUserNotificationsAsRead',

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
            'run/mention-user',
            'run/delete-attachment'  => [
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

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Set default template
        $template = new \App\Model\Template(
            path: '/Styles',
            template: $system->get('site.template')
        );

        // If in session is saved template to preview
        if (\App\Model\Session::exists('preview'))
        {
            // If this template really exists
            if (file_exists(ROOT . '/Styles/' . \App\Model\Session::get('preview')))
            {
                // Set preview template
                $template = new \App\Model\Template(
                    path: '/Styles',
                    template: \App\Model\Session::get('preview')
                );

                $data->set('data.preview', \App\Model\Session::get('preview'));
            }
        }

        // Set default language
        $language = $data->get('inst.language');
        $language->load( language: $system->get('site.language'), template: $template, folder: 'website' );

        // Put language to visualizators
        \App\Visualization\Visualization::$language = $language;

        // Put system model to file
        \App\Model\File\File::$system = $system;

        // Set default locale and timezone
        setlocale(LC_ALL, $system->get('site.locale') . '.UTF-8');
        date_default_timezone_set($system->get('site.timezone'));

        // Check for ajax
        $this->checkForAjax();

        // Set page favicon
        $favicon = '/Uploads/Site/PHPCore_icon.svg';
        if ($system->get('site.favicon'))
        {
            $favicon = '/Uploads/Site/Favicon.' . $system->get('site.favicon');
        }
        $data->set('data.head.favicon', $favicon);
        
        // Default page title
        $data->set('data.head.title', $system->get('site.name'));

        // Default page description
        $data->set('data.head.description', $system->get('site.description'));
        
        // Navbar
        $navbar = new \App\Visualization\Navbar\Navbar('Root/Navbar:/Formats/Basic.json');

        // Setup custom menu
        // Fill menu with custom buttons
        $navbar->elm1('menu')->fill(data: $db->select('app.button.all()'), function: function ( \App\Visualization\Navbar\Navbar $navbar ) use ($db)
        {
            // If button is dropdown
            if ($navbar->get('data.button_dropdown') == 1)
            {
                // Set button type to dropdown
                $navbar->set('options.type', 'dropdown');

                // Fill dropdown with buttons
                $navbar->fill(data: $db->select('app.sub-button.parent()', $navbar->get('data.button_id')));
            }
        });
        
        // If user is logged
        if ($user->isLogged())
        {
            // If mark parametr is set in url
            if ($this->url->is('mark'))
            {
                // Set user notification as read
                $db->query('
                    DELETE un FROM ' . TABLE_USERS_NOTIFICATIONS . '
                    WHERE user_notification_id = ? AND to_user_id = ?
                ', [$this->url->get('mark'), LOGGED_USER_ID]);
            }
        }
        
        // Set part of menu for logged users
        $navbar->elm1('logged', function ( \App\Visualization\Navbar\Navbar $navbar ) use ($user, $system, $db, $language)
        {
            // If user is logged
            if ($user->isLogged())
            {
                // Show this part of navbar
                $navbar->show();

                $navbar
                    // Set number of unreaded messages to navbar
                    ->elm2('conversation')->set('data.notifiCount', count((array)$user->get('unread')))
                    // Setup user in navbar
                    ->elm2('user')
                        // data.user_image - User's profile image 
                        ->set('data.user_image', $this->build->user->image(data: $user->get(), size: '20x20'))
                        // data.user_name  - User's name
                        ->set('data.user_name', $user->get('user_name'))
                        // data.class - User's class
                        ->set('data.class', 'user--' . $user->get('group_class'))
                        // Setup button in dropdown to profile page
                        ->elm3('profile')
                            // data.href - Link to user's profile
                            ->set('data.href', $navbar->url($this->build->url->profile($user->get())));

                // If system has enabled blog mode
                if ($system->get('site.mode') == 'blog')
                {
                    $navbar
                        // Hide notifications
                        ->elm2('notification')->hide()
                        // Hide conversations
                        ->elm2('conversation')->hide();
                }

                // If system has enabled static mode
                if ($system->get('site.mode') == 'static')
                {
                    $navbar
                        // Hide notifications
                        ->elm2('notification')->hide()
                        // Hide conversations
                        ->elm2('conversation')->hide()

                        ->elm2('user')
                            // Delete profile image
                            ->set('data.user_image', '')
                            // Hide profile
                            ->elm3('profile')->hide()
                            // Hide user panle
                            ->elm3('user')->hide();
                }

                // If profiles are disabled
                if ($system->get('site.mode.blog.profiles') == 0)
                {
                    $navbar->elm2('user')
                        // Delete user image from navbar
                        ->delete('data.user_image')
                        // Hide button in dropdown with link to profile page
                        ->elm3('profile')->hide();
                }

                // If user has any unreaded notifications
                // Get notifications
                $notifications = $db->select('app.user-notification.parent()', LOGGED_USER_ID);
                
                $navbar->elm2('notification')
                    // Set number of unreaded notifications
                    ->set('data.notifiCount', count($notifications));

                // Fill dropdown with notifications
                $navbar->elm2('notification')->fill(data: $notifications, function: function ( \App\Visualization\Navbar\Navbar $navbar ) use ($language)
                {
                    $url = match($navbar->get('data.user_notification_item'))
                    {
                        'likePost', 'newPost' => 'post',

                        'likeTopic',
                        'stickTopic',
                        'unstickTopic',
                        'lockTopic',
                        'deletePost',
                        'unlockTopic' => 'topic',

                        'deleteTopic' => 'forum',

                        'deleteProfilePostComment', 'newProfilePost' => 'profilePost',

                        'deleteProfilePost' => 'profile',

                        'newProfilePostComment' => 'profilePostComment'
                    };

                    // If user notifiation relates to topic or post
                    if (in_array($url, ['post', 'topic', 'forum']))
                    {
                        // And logged user doesn't have permission to look at this forum or category
                        if (!array_intersect([LOGGED_USER_GROUP_ID, '*'], $navbar->get('data.permission_see')))
                        {
                            // Show unavailable message
                            $navbar->set('data.body', $language->get('L_NAVBAR.L_NOTIFICATION.L_NOT_AVAILABLE'));

                            // End method
                            return;
                        }
                    }

                    // Get username and profile image of user who "sent" this notification
                    $userName = $this->build->user->link(data: $navbar->get('data'));
                    $userImage = $this->build->user->image($navbar->get('data'));

                    // Get body of notification
                    $body = $language->get('L_NAVBAR.L_NOTIFICATION.L_NOTIFICATION.' . $navbar->get('data.user_notification_item'));

                    // Date of send notification
                    $date = $this->build->date->long($navbar->get('data.user_notification_created'), true);

                    $name = '';
                    // If notification relates to topic or post
                    // Becaouse only this notification has some "title" or let's say "item name"
                    if ($navbar->get('data.topic_name'))
                    {
                        // Set name
                        $name = ' <span class="fw-600">' . $navbar->get('data.topic_name') . '</span>';
                    }

                    if (in_array($url, ['profilePost', 'profilePostComment', 'profile']))
                    {
                        $navbar->set('data.user_id', $navbar->get('data.profile_user_id'));
                        $navbar->set('data.user_name', $navbar->get('data.profile_user_name'));
                    }

                    // Build the final url according to the $process
                    $url = $this->build->url->{$url}($navbar->get('data'));

                    // Build show button
                    $showButton = '<a class="show" href="' . $url . '">' . $language->get('L_BTN.L_SHOW') . '</a>';

                    // Compile the whole notification
                    $navbar->set('data.body', $userImage . '<div class="inner">' . $userName . ' ' . $body . $name . $showButton . '<br>' . $date . '</div>');
                });
            }
        });

        // Setup part of navabr which is available for not-logged users
        $navbar->elm1('not-logged', function ( \App\Visualization\Navbar\Navbar $navbar ) use ($user, $system)
        {
            // If user is not logged
            if ($user->isLogged() === false)
            {
                // Show this part of navbar
                $navbar->show();

                // Move to register button
                $navbar->elm2('register', function ( \App\Visualization\Navbar\Navbar $navbar ) use ($system)
                {
                    // If system has allowed registration
                    if ($system->get('registration.enabled') == 1)
                    {
                        // Show this button
                        $navbar->show();
                    } 
                });
            }
        });

        // Get navbar data to generate
        $data->navbar = $navbar->getDataToGenerate();

        // If logged user has permission to manage templates
        if ($permission->has('admin.template'))
        {
            // If template has any setup page
            if (file_exists(ROOT . '/Styles/' . $template->get('template') . '/Object/Page/Index.page.php'))
            {
                // Show button to setup template
                $data->set('options.setupTemplate', true);
            }
        }

        // If logged user has permission to admin panel
        if ($permission->has('admin.?'))
        {
            // Show button to admin panel
            $data->set('options.adminAccess', true);
        }

        if ($system->get('site.mode') == 'static')
        {
            $class = '\App\Page\Custom\Index';
            if (in_array('edit', $this->url->get()))
            {
                $class = '\App\Page\Custom\Edit';
            }

            if (in_array('login', $this->url->get()))
            {
                $class = '\App\Page\Login';
            }

            // Initialise page
            $page = $this->buildPage( class: $class );
            
            // Load page
            $page->body( $data, $db );
            
        } else {
            
            // Save loaded page class
            $page = $this->buildPage();

            // Load page
            $page->body( $data, $db );

            // Check if user approached to ajax
            if (str_starts_with(get_class($page), 'App\Page\Get\\'))
            {
                exit();
            }

            // Check for ajax
            $page->checkForAjax();
        }
        
        // If any plugins wants to change some content on loaded page
        // Loads this plugins
        $page->loadPageFromPlugins();
        
        // File model
        $file = new \App\Model\File\File();
            
        // Foreach every installed plugin
        foreach (LIST_OF_INSTALLED_PLUGINS as $item)
        {
            // If exists any plugin which want to change content on every page = \Page\Router
            // If this page exists in foreached plugin
            if ($file->exists('/Plugins/' . $item . '/Object/Page/Router.page.php'))
            {
                // Build plugin page
                $page = $this->buildPage( class: 'Plugin\\' . $item . '\Page\Router' );

                // Load plugin page
                $page->body( $this->data, $this->db );
            }
        }

        if (in_array(get_class($page), ['App\Page\Index', 'App\Page\Forum\Index']))
        {
            $sidebar = new \App\Visualization\Sidebar\Sidebar($data->sidebar);

            // Sort the sidebar objects as they are set in the admin panel
            $sidebar->sort(array_column($db->select('app.sidebar.all()'), 'sidebar_object'));

            // Save sidebar and get ready to generate
            $data->sidebar = $sidebar->getDataToGenerate();
        }

        $this->checkFormSubmit();

        // End page
        $this->end();
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
    public function windowDelete( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Language
        $language = $data->get('inst.language');

        return [
            'title' => $language->get('L_WINDOW.L_TITLE.L_CONFIRM'),
            'close' => $language->get('L_NO'),
            'submit' => $language->get('L_YES'),
            'content' => $language->get('L_WINDOW.L_DESC.' . $post->get('ajax'))
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
    public function windowReport( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Language
        $language = $data->get('inst.language');

        return [
            'title' => $language->get('L_WINDOW.L_TITLE.L_REPORT'),
            'close' => $language->get('L_BTN.L_CANCEL'),
            'submit' => $language->get('L_BTN.L_SUBMIT'),
            'content' => $language->get('L_WINDOW.L_DESC.L_REPORT') . '<br><textarea></textarea>'
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
    public function deleteAttachment( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // File
        $file = new \App\Model\File\File();
       
        if (!$file->exists($post->get('id')))
        {
            return false;
        }
        
        $file->delete($post->get('id'));

        $explode = array_values(array_filter(explode('/', $post->get('id'))));

        if (count($explode) <= 2)
        {
            return;
        }

        // If attachment is from topic
        if ($explode[0] == 'Uploads' and $explode[1] == 'Topics')
        {
            // Update number of attachments in database
            $db->query('UPDATE ' . TABLE_TOPICS . ' SET topic_attachments = topic_attachments - 1 WHERE topic_id = ?', [$explode[2]]);
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
    public function logoutUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Update user data
        $db->update(TABLE_USERS, [
            'user_hash' => md5(uniqid(mt_rand(), true)),
            'user_last_activity' => DATE_DATABASE
        ], LOGGED_USER_ID);

        // Delete cookies
        \App\Model\Cookie::delete('token');

        // Delete sessions
        \App\Model\Session::delete('token');

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
    public function markUserNotificationsAsRead( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Mark all user notifications ad read
        $db->query('
            DELETE un FROM ' . TABLE_USERS_NOTIFICATIONS . '
            WHERE to_user_id = ?
        ', [LOGGED_USER_ID]);

        // Content
        $content = new \App\Model\Content();

        return [
            'content' => $content->get( url: 'Root/Style:/Templates/Blocks/Visualization/Navbar/Notification/NotificationEmpty.phtml' )
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
    public function closePreview( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Delete template from session
        \App\Model\Session::delete('preview');

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
    public function mentionUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if ($system->get('site.mode.blog.profiles') == 0)
        {
            return;
        }

        $row = $db->select('app.user.byName()', $post->get('id'));
        if (!$row)
        {
            return false;
        }

        // Language
        $language = $data->get('inst.language');

        $roleHTML = '';
        $roles = $db->select('app.role.parent()', $row['user_roles']);
        foreach ($roles as $r)
        {
            $roleHTML .= '<div class="role role--' . $r['role_class'] . '">' . ($r['role_icon'] ? '<i class="' . $r['role_icon'] . '"></i>' : '') . '<span>' . $r['role_name'] . '</span></div>';
        }

        return [
            'name' => $this->build->user->link($row),
            'image' => $this->build->user->image(data: $row, size: '90x90'),
            'background' => '/Uploads/Users/' . $row['user_id'] . '/Header.' . $row['user_header_image'],
            'group' => $this->build->user->group($row),
            'role' => $roleHTML,
            'reputation' => $row['user_reputation'] ? '<div class="reputation" ><i class="fa-solid fa-thumbs-up"></i> ' . $row['user_reputation'] . '</div>' : '',
            'posts' => [
                'lang' => $language->get('L_POSTS'),
                'count' => $row['user_posts']
            ],
            'topics' => [
                'lang' => $language->get('L_TOPICS'),
                'count' => $row['user_topics']
            ]
        ];
    }
}