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

namespace Model\Build;

use Model\Url;

/**
 * BuildUser 
 */
class BuildUser extends Build
{    
    /**
     * @var array $settins Settings for user notifications
     */
    private array $settings = [
        'Topic' => [
            'max' => null,
            'name' => 'topic_name'
        ],
        'Post' => [
            'max' => MAX_POSTS,
            'name' => 'topic_name'
        ],
        'ProfilePost' => [
            'max' => MAX_PROFILE_POSTS
        ],
        'ProfilePostComment' => [
            'max' => MAX_PROFILE_POSTS
        ]
    ];
    
    /**
     * Assigns prefix to data
     *
     * @param  array $data
     * @param  string $prefix
     * 
     * @return array
     */
    private function assignPrefix( array $data, string $prefix )
    {
        foreach (['user_id', 'group_class_name', 'user_name', 'user_deleted', 'user_profile_image'] as $input) {
            $data[$input] = $data[$prefix.$input] ?? '';
        }
        return $data;
    }
    
    /**
     * Builds user information block
     *
     * @param  array $data User data [user_deleted, user_id, user_profile_image, user_name, user_reputation, user_posts, ?user_last_activity]
     * @param  array $online If true - returned image will have online indicator
     * @param  array $prefix Prefix for data
     * 
     * @return string
     */
    public function info( array $data, bool $online = false, string $prefix = '' )
    {
        return '
        <div class="user vertical-align">
            ' . $this->image(data: $data, online: $online, prefix: $prefix) . '
            <div>
                ' . $this->link(data: $data, prefix: $prefix) . '
                <br>
                <span class="grey">' . $this->language->get('L_USER_REPUTATION') . ': <span class="fw-600">' . $data[$prefix.'user_reputation'] . ' </span>' . $this->language->get('L_POSTS') . ': <span class="fw-600">' . $data[$prefix.'user_posts'] . '</span></span>
            </div>
        </div>';
    }

    /**
     * Builds user profile image
     *
     * @param  array $data User data [user_deleted, user_id, user_profile_image, user_name, ?user_last_activity]
     * @param  array $online If true - returned image will have online indicator
     * @param  array $prefix Prefix for data
     * 
     * @return string
     */
    public function image( $data, bool $online = false, string $prefix = '' )
    { 
        $data = $this->assignPrefix($data, $prefix);
        if (isset($data['user_deleted']) and $data['user_deleted'] == 1) {
            $data['user_name'] = $this->language->get('L_DELETED_USER');
            $data['user_profile_image'] = 'grey';
        }

        $img = '<div class="profile" ajax-selecotor="profile">' . (($online === true) ? $this->online((string)$data['user_last_activity']) : '');
        if (in_array($format = explode('?', $data['user_profile_image'])[0], ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {
            if (file_exists(ROOT . '/Uploads/User/' . $data['user_id'] . '/Profile.' . $format)) {
                return $img .'<span class="profile-image"><img src="/Uploads/User/' . $data['user_id'] . '/Profile.' . $data['user_profile_image'] . '" alt="' . $this->language->get('L_PROFILE_IMAGE') . '"></span></div>';
            }
        }
        
        return $img . '<span class="profile-image profile-image-' . $data['user_profile_image'] . '"><span>' . strtoupper(substr($data['user_name'], 0, 1)) . '</span></span></div>';
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
        if (time() < strtotime('+60 seconds', strtotime($date))) {
            return '<div class="online"></div>';
        }

        return '';
    }
    
    /**
     * Builds user notification
     *
     * @param  array $data User notification data
     * 
     * @return string
     */
    public function notification( array $data )
    {
        $ex = explode('/', $data['user_notification_type']);
        $processFile = $ex[count($ex)-2];

        $action = $ex[count($ex)-1];

        $_block = '\Block\\' . $processFile;
        $block = new $_block();

        if (!$queryResult = $block->getUN($data['user_notification_type_id'])) {
            return false;
        }

        $result = $this->link($data) . ' ' . $this->language->get('L_NAVBAR')['L_NOTIFICATION'][$data['user_notification_type']];

        $name = $queryResult[$this->settings[$processFile]['name'] ?? ''] ?? '';
        if ($name) {
            $result .= ' <span class="fw-600">' . $name . '</span>';
        }

        // URL TO CONTENT
        $type = $processFile;
        if ($action == 'Delete') {
            $type = match($type) {
                'ProfilePostComment' => 'ProfilePost',
                'ProfilePost' => 'Profile',
                'Post' => 'Topic',
                'Topic' => 'Forum'
            };
        }

        $url = $this->url->{lcfirst($type)}(array_merge($data, $queryResult));

        $result .= '<a class="show" href="' . Url::build($url) . '">' . $this->language->get('L_BUTTON')['L_SHOW'] . '</a>';


        return $result;
    }
    
    /**
     * Builds username with link to profile
     *
     * @param  array $data User data [user_deleted, user_name, user_id, ?group_class_name]
     * @param  bool $groupColor If true - link will have color as group
     * @param  string $prefix Prefix for data
     * 
     * @return string
     */
    public function link( array $data, bool $groupColor = false, string $prefix = '' )
    {
        $data = $this->assignPrefix($data, $prefix);
        
        if (isset($data['user_deleted']) and $data['user_deleted'] == 1) {
            $data['user_name'] = $this->language->get('L_DELETED_USER');
        }

        return '<a class="username' . ($groupColor === true ? ' user--' . $data['group_class_name'] : '') . '" ajax-selector="user_name" ' . ((isset($data['user_deleted']) and $data['user_deleted'] == 0) ? 'href="' . $this->url->profile($data) . '"' : '') . '>' . $data['user_name'] . '</a>';
    }

    /**
     * Builds username and profile image with link to profile
     *
     * @param  array $data User data [user_deleted, user_name, user_id, group_class_name]
     * 
     * @return string
     */
    public function linkImg( array $data )
    {
        if (isset($data['user_deleted']) and $data['user_deleted'] == 1) {
            $data['user_name'] = $this->language->get('L_DELETED_USER');
        }

        return '<a class="username vertical-align user--' . $data['group_class_name'] . ' fw-700" ' . ((isset($data['user_deleted']) and $data['user_deleted'] == 0) ? 'href="' . $this->url->profile($data) . '"' : '') . '>' . $this->image($data) . '<span>' . $data['user_name'] . '</span></a>';
    }
}