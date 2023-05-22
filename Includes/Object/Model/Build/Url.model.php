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
 * Url
 */
class Url
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
        return \App\Model\Url::build($url);
    }

    /**
     * Builds url to conversation
     *
     * @param  array $data Conversation data [conversation_name]
     * 
     * @return string
     */
    public function conversation( $data )
    {
        return $this->convertToURL('/user/conversation/show/' . $data['conversation_url']);
    }

    /**
     * Builds url to article
     *
     * @param  array $data Post data [article_url, user_notification_id]
     * 
     * @return string
     */
    public function article( array $data  )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_POSTS) : 1;
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';

        return $this->convertToURL('/article/' . $data['article_url']);
    }

    /**
     * Builds url to post
     *
     * @param  array $data Post data [topic_url, post_id, position, user_notification_id]
     * 
     * @return string
     */
    public function post( $data )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_POSTS) : 1;
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';

        return $this->convertToURL('/forum/topic/' . $data['topic_url'] . '/page-' . $page . '/select-' . $data['post_id'] . '/' . $mark . '#' . $data['post_id']);
    }

    /**
     * Builds url to topic
     *
     * @param  array $data Topic data [topic_url, user_notification_id]
     * 
     * @return string
     */
    public function topic( array $data )
    {
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';
        return $this->convertToURL('/forum/topic/' . $data['topic_url'] . '/' . $mark);
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
     * @param  array $data Profile post data [profile_user_id, profile_user_name, profile_post_id, position, user_notification_id]
     * 
     * @return string
     */
    public function profilePost( $data )
    {
        $page = isset($data['position']) ? ceil($data['position'] / MAX_PROFILE_POSTS) : 1;
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';

        return $this->convertToURL('/profile/' . $data['profile_user_id'] . '.' . $data['profile_user_name'] . '/page-' . $page . '/select-' . $data['profile_post_id'] . '/' . $mark . '#' . $data['profile_post_id']);
    }

    /**
     * Builds url to profile post comment
     *
     * @param  array $data Profile post comment data [profile_user_id, profile_user_name, profile_post_id, profile_post_comment_id, profile_post_position, user_notification_id]
     * 
     * @return string
     */
    public function profilePostComment( $data )
    {
        $page = isset($data['profile_post_position']) ? ceil((int)$data['profile_post_position'] / MAX_PROFILE_POSTS) : 1;
        $mark = isset($data['user_notification_id']) ? 'mark-' . $data['user_notification_id'] . '/' : '';

        return $this->convertToURL('/profile/' . $data['profile_user_id'] . '.' . $data['profile_user_name'] . '/page-' . $page . '/select-p' . $data['profile_post_id'] . '.c' . $data['profile_post_comment_id'] . '/' . $mark . '#c' . $data['profile_post_comment_id']);
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