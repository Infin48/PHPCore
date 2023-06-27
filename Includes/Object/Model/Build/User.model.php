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

namespace App\Model\Build;

/**
 * User 
 */
class User
{
    /**
     * @var \App\Model\Language $language Language instance
     */
    private \App\Model\Language $language;

    /**
     * @var \App\Model\System $system System instance
     */
    private \App\Model\System $system;

    /**
     * @var \App\Model\Build\Url $url Url
     */
    private \App\Model\Build\Url $url;
    
    /**
     * Constructor
     *
     * @param  \Model\Language $language Language instance
     * @param  \Model\System $system System instance
     */
    public function __construct( \App\Model\Language $language, \App\Model\System $system )
    {
        $this->url = new \App\Model\Build\Url();

        $this->system = $system;
        $this->language = $language;
    }

    /**
     * Returns reputation block
     *
     * @param  int $reputation User reputation
     * 
     * @return string
     */
    public function reputation( int $reputation )
    {
        // If user did not received any reputation yet
        if ($reputation <= 0)
        {
            // End method
            return '';
        }

        // Return reputation
        return '<div class="reputation" ajax="title" ajax-title="' . $this->language->get('L_USER.L_REPUTATION') . '" ajax-class="reputation"><i class="fa-solid fa-thumbs-up"></i> ' . $reputation . '</div>';
    }

    /**
     * Returns user group
     *
     * @param  array $data Group data [group_class, group_name, user_deleted]
     * 
     * @return string
     */
    public function group( array $data )
    {
        // Check if method has all required data
        foreach (['group_class', 'group_name', 'user_deleted'] as $item)
        {
            // If any data missing
            if (!isset($data[$item]))
            {
                // End method
                return '';
            }
        }

        // If user is deleted
        if ($data['user_deleted'] == 1)
        {
            // End method
            // Deleted user has no assigned group
            return '';
        }

        // Retrun group
        return '<div class="statue statue-small statue--' . $data['group_class'] . '">' . $data['group_name'] . '</div>';
    }

    /**
     * Builds user profile image
     *
     * @param  array $data User data [user_deleted, user_id, user_profile_image, user_name, ?user_last_activity, ?role_name, ?role_class]
     * @param  bool $online If true - returned image will have online indicator
     * @param  bool $role If true - returned image will have primary role
     * 
     * @return string
     */
    public function image( array $data, bool $online = false, bool $role = false, string $size = '30x30' )
    {
        // Check if method has all required data
        foreach ($required = ['user_deleted', 'user_id', 'user_profile_image', 'user_name'] as $item)
        {
            // If any data missing
            if (!isset($data[$item]))
            {
                // End method
                return '';
            }
        }

        // If user user image to build is deleted
        if ($data['user_deleted'] == 1)
        {
            // Set username to "unknown" and  profile image to grey
            $data['user_name'] = $this->language->get('L_DELETED_USER');
            $data['user_profile_image'] = 'grey';

            // Hide online indicator
            $online = false;
        }

        // If method has data about user's last activity
        if (isset($data['user_last_activity']))
        {
            // If online indicator is set to show
            if ($online)
            {
                // Build online indicator
                $online = $this->online($data['user_last_activity']);
            }
        }

        $img = '<div class="profile" js="profile">';
        
        // Default role value
        $_role = '';
        
        // Role is set to show
        if ($role == true)
        {
            while (true)
            {
                // Check if method has all required data to show role
                foreach (['role_name', 'role_class'] as $item)
                {
                    // If method has not any data
                    if (!isset($data[$item]) or empty($data[$item]))
                    {
                        // Stop building role
                        break 2;
                    }
                }

                // Set default content value to first letter of role
                // Or if role has set icon show it instead of the letter
                $content = mb_substr($data['role_name'], 0, 1);
                if (!empty($data['role_icon']))
                {
                    $content = '<i class="' . $data['role_icon'] . '"></i>';
                }

                // Compile the whole role
                $_role = '<div class="role role-primary role--' . $data['role_class'] . '" ajax="title" ajax-class="role--' . $data['role_class'] . '" ajax-title="' . $data['role_name'] . '">' . $content . '</div>';
                
                break;
            }
        }

        // Explode size values
        // $_size[0] = width
        // $_size[1] = height
        $_size = explode('x', $size);

        // If user has uplaoded own profile iamge
        // Get the extension of image
        $format = explode('?', $data['user_profile_image'])[0];

        // If user image really exists
        if (file_exists(ROOT . '/Uploads/Users/' . $data['user_id'] . '/Profile.' . $format))
        {
            // Set size attributes
            $attr = 'width="' . $_size[0] . '" height="' . $_size[1] . '"';

            // Return compiled users's profile image with own image
            return $img .'<span class="profile-image profile-image-' . $size . '">' . $online . '<img src="/Uploads/Users/' . $data['user_id'] . '/Profile.' . $data['user_profile_image'] . '" ' . $attr . ' alt="' . $this->language->get('L_USER.L_PROFILE_IMAGE.L_PROFILE_IMAGE') . '"></span>' . $_role . '</div>';
        }
        
        // Return compiles user's profile image with pre-defined image
        return $img . '<span class="profile-image profile-image-' . $data['user_profile_image'] . ' profile-image-' . $size . '">' . $online . '<span>' . strtoupper(substr($data['user_name'], 0, 1)) . '</span></span>' . $_role . '</div>';
    }
    
    /**
     * Builds online indicator
     *
     * @param  string $date
     * 
     * @return string
     */
    public function online( string $date )
    {
        // If minute has not passed after last recorded user activity
        if (time() < strtotime('+60 seconds', strtotime($date)))
        {
            // Return online indicator
            return '<div class="online" ajax="title" ajax-title="' . $this->language->get('L_ONLINE') . '" ajax-class="online"></div>';
        }

        return '';
    }
    
    /**
     * Builds link to user profile
     *
     * @param  array $data User data [user_deleted, user_name, user_id, group_class]
     * @param  bool $group If true - usernaeme will has the same color as group
     * @param  string $href Sets href to link
     * 
     * @return string
     */
    public function link( array $data, bool $group = true, string $href = null )
    {
        // List of requred data
        $required = ['user_deleted', 'user_id', 'user_name'];

        // If allowed to color the username to color of group 
        if ($group == true)
        {
            array_push($required, 'group_class');
        }

        // Check if method has all required data
        foreach ($required as $item)
        {
            // If any data missing
            if (!isset($data[$item]))
            {
                // End method
                return '';
            }
        }

        // If profiles are enabled
        if ($this->system->get('site_mode_blog_profiles'))
        {
            // If href was not passed manually
            if (!$href)
            {
                // Set href to profile
                $href = $this->url->profile($data);
            }
            
            // Compile href attribute
            $href = 'href="' . $href . '"';
        }
        
        // If user is deleted
        if ($data['user_deleted'] == 1)
        {
            // Set user username to "unknown"
            $data['user_name'] = $this->language->get('L_DELETED_USER');

            // Change user class to "deleted"
            $data['group_class'] = 'deleted';

            // Erase href attribute
            $href = '';
        }

        // Return compiled link to user profile
        return '<a class="username ' . ($group ? 'user--' . $data['group_class'] : '') . '" js="user_name" ' .  $href . '>' . $data['user_name'] . '</a>';
    }

    /**
     * Builds username and profile image with link to profile
     *
     * @param  array $data User data [user_deleted, user_name, user_id, group_class]
     * 
     * @return string
     */
    public function linkImg( array $data, bool $group = false, bool|string $role = false, string $size = '30x30' )
    {
        // Check if method has all required data
        foreach (['user_deleted', 'user_id', 'user_name', 'group_class'] as $item)
        {
            // If any data missing
            if (!isset($data[$item]))
            {
                // End method
                return '';
            }
        }

        // Return compiled user profile image with link
        return '<div class="vertical-align">' . $this->image(data: $data, role: $role, size: $size) . '<div>' . $this->link(data: $data) . ($group ? '<br>' . $this->group(data: $data) : '') . '</div></div>';
    }
}