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
        return $this->system->url->build($url);
    }

    /**
     * Builds url to post
     *
     * @param  array $data Post data [topic_id, topic_url, post_id, ?position]
     * 
     * @return string
     */
    public function post( $data )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_POSTS) : 1;

        return $this->convertToURL('/forum/topic/' . $data['topic_id'] . '.' . $data['topic_url'] . '/page-' . $page . '/select-' . $data['post_id'] . '/#' . $data['post_id']);
    }

    /**
     * Builds url to topic
     *
     * @param  array $data Topic data [topic_id, topic_url]
     * 
     * @return string
     */
    public function topic( $data )
    {
        return $this->convertToURL('/forum/topic/' . $data['topic_id'] . '.' . $data['topic_url']);
    }

    /**
     * Builds url to forum
     *
     * @param  array $data Forum data [forum_id, forum_url]
     * 
     * @return string
     */
    public function forum( $data )
    {
        return $this->convertToURL('/forum/show/' . $data['forum_id'] . '.' . $data['forum_url']);
    }

    /**
     * Builds url to profile post
     *
     * @param  array $data Profile post data [profile_user_id, profile_user_name, profile_post_id, ?position]
     * 
     * @return string
     */
    public function profilePost( $data )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_PROFILE_POSTS) : 1;

        return $this->convertToURL('/profile/' . $data['profile_user_id'] . '.' . $data['profile_user_name'] . '/page-' . $page . '/select-' . $data['profile_post_id'] . '/#' . $data['profile_post_id']);
    }

    /**
     * Builds url to profile post comment
     *
     * @param  array $data Profile post comment data [profile_user_id, profile_user_name, profile_post_comment_id, ?position]
     * 
     * @return string
     */
    public function profilePostComment( $data )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_PROFILE_POSTS) : 1;

        return $this->convertToURL('/profile/' . $data['profile_user_id'] . '.' . $data['profile_user_name'] . '/page-' . $page . '/select-c' . $data['profile_post_comment_id'] . '/#c' . $data['profile_post_comment_id']);
    }

    /**
     * Builds url to user profile
     *
     * @param  array $data User profile data [user_id, user_name]
     * 
     * @return string
     */
    public function profile( $data )
    {
        return $this->convertToURL('/profile/' . $data['user_id'] . '.' . $data['user_name']);
    }
}