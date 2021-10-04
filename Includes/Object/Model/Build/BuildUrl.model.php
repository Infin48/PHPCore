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
 * BuildUrl
 */
class BuildUrl extends Build
{    
    /**
     * Converts url
     *
     * @param  string $url Url to convert
     * 
     * @return string
     */
    private function convertToURL( string $url )
    {
        return Url::build($url);
    }

    /**
     * Builds url to post
     *
     * @param  array $data Post data [topic_id, topic_url, post_id, position, user_notification_id]
     * 
     * @return string
     */
    public function post( $data )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_POSTS) : 1;
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';

        return $this->convertToURL('/forum/topic/' . $data['topic_id'] . '.' . $data['topic_url'] . '/page-' . $page . '/select-' . $data['post_id'] . '/' . $mark . '#' . $data['post_id']);
    }

    /**
     * Builds url to topic
     *
     * @param  array $data Topic data [topic_id, topic_url, user_notification_id]
     * 
     * @return string
     */
    public function topic( $data )
    {
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';
        return $this->convertToURL('/forum/topic/' . $data['topic_id'] . '.' . $data['topic_url'] . '/' . $mark);
    }

    /**
     * Builds url to forum
     *
     * @param  array $data Forum data [forum_id, forum_url, user_notification_id]
     * 
     * @return string
     */
    public function forum( $data )
    {
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';
        return $this->convertToURL('/forum/show/' . $data['forum_id'] . '.' . $data['forum_url'] . '/' . $mark);
    }

    /**
     * Builds url to profile post
     *
     * @param  array $data Profile post data [user_id, user_name, profile_post_id, position, user_notification_id]
     * 
     * @return string
     */
    public function profilePost( $data )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_PROFILE_POSTS) : 1;
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';

        return $this->convertToURL('/profile/' . $data['user_id'] . '.' . $data['user_name'] . '/page-' . $page . '/select-' . $data['profile_post_id'] . '/' . $mark . '#' . $data['profile_post_id']);
    }

    /**
     * Builds url to profile post comment
     *
     * @param  array $data Profile post comment data [user_id, user_name, profile_post_id, profile_post_comment_id, position, user_notification_id]
     * 
     * @return string
     */
    public function profilePostComment( $data )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_PROFILE_POSTS) : 1;
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';

        return $this->convertToURL('/profile/' . $data['user_id'] . '.' . $data['user_name'] . '/page-' . $page . '/select-p' . $data['profile_post_id'] . '.c' . $data['profile_post_comment_id'] . '/' . $mark . '#c' . $data['profile_post_comment_id']);
    }

    /**
     * Builds url to user profile
     *
     * @param  array $data User profile data [user_id, user_name, user_notification_id]
     * 
     * @return string
     */
    public function profile( $data )
    {
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';
        return $this->convertToURL('/profile/' . $data['user_id'] . '.' . $data['user_name'] . '/' . $mark);
    }
}