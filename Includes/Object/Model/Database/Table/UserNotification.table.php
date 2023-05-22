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

namespace App\Table;

/**
 * UserNotification
 */
class UserNotification extends Table
{    
    /**
     * Returns all users notifications from user
     *
     * @param  int $ID User ID
     * 
     * @return array
     */
    public function parent( int $ID )
    {
        $notifications =  $this->db->query('
            SELECT un.*, t.topic_name, u2.user_name AS profile_user_name, u2.user_id AS profile_user_id, u2.user_deleted AS profile_user_deleted, ' . $this->select->user() . ', IFNULL(pp.profile_post_id, ppc.profile_post_id) AS profile_post_id, ppc.profile_post_comment_id, p.post_id, t.topic_url, t.topic_id, cp.permission_see AS permission_see_category, fp.permission_see AS permission_see_forum
            FROM ' . TABLE_USERS_NOTIFICATIONS . '
            ' . $this->join->user('un.user_id'). '
            LEFT JOIN ' . TABLE_POSTS . ' ON p.post_id = un.user_notification_item_id AND (un.user_notification_item LIKE "%Post%" AND un.user_notification_item NOT LIKE "%ProfilePost%")
            LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = IFNULL(p.topic_id, un.user_notification_item_id) AND (un.user_notification_item LIKE "%Topic%" OR un.user_notification_item LIKE "%\Post%" AND un.user_notification_item NOT LIKE "%ProfilePost%")
            LEFT JOIN ' . TABLE_PROFILE_POSTS_COMMENTS . ' ON ppc.profile_post_comment_id = un.user_notification_item_id AND un.user_notification_item LIKE "%ProfilePostComment%"
            LEFT JOIN ' . TABLE_PROFILE_POSTS . ' ON pp.profile_post_id = un.user_notification_item_id AND un.user_notification_item LIKE "%ProfilePost%"
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id = IFNULL(ppc.profile_id, pp.profile_id) AND (un.user_notification_item LIKE "%ProfilePost%" OR un.user_notification_item LIKE "%ProfilePostComment%")
            LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id
            WHERE un.to_user_id = ?
            ORDER BY user_notification_created DESC',
        [$ID], ROWS);

        foreach ($notifications as &$notification)
        {
            $notification['permission_see_forum'] = explode(',', $notification['permission_see_forum']);
            $notification['permission_see_category'] = explode(',', $notification['permission_see_category']);

            if (in_array('*', $notification['permission_see_forum']))
            {
                $notification['permission_see_forum'] = $notification['permission_see_category'];
            }

            if (in_array('*', $notification['permission_see_category']))
            {
                $notification['permission_see_category'] = $notification['permission_see_forum'];
            }

            $notification['permission_see'] = array_intersect($notification['permission_see_category'], $notification['permission_see_forum']);
        }

        return $notifications;
    }
}