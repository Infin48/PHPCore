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
 * User
 */
class User extends Table
{
    /**
     * Returns user by user ID
     *
     * @param  int $ID User ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('
            SELECT u.*, g.group_name, g.group_class, g.group_index, fp.forgot_code, va.account_code, ve.email_code, va.account_code_sent, ve.email_code_sent
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_FORGOT . ' ON fp.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_ACCOUNT . ' ON va.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_EMAIL . ' ON ve.user_id = u.user_id
            WHERE u.user_id = ? AND user_deleted = 0
        ', [$ID]);
    }

    /**
     * Returns user by user name
     *
     * @param  string $name User name
     * 
     * @return array
     */
    public function byName( string $userName )
    {
        return $this->db->query('
            SELECT u.*, g.group_name, g.group_class, g.group_index, fp.forgot_code, va.account_code, va.account_code_sent
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_FORGOT . ' ON fp.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_ACCOUNT . ' ON va.user_id = u.user_id
            WHERE u.user_name = ?
        ', [$userName]);
    }

    /**
     * Returns user by user e-mail
     *
     * @param  string $userEmail User e-mail
     * 
     * @return array
     */
    public function byEmail( string $userEmail )
    {
        return $this->db->query('
            SELECT u.*, g.group_name, g.group_class, g.group_index, fp.forgot_code, fp.forgot_code_sent, va.account_code
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_FORGOT . ' ON fp.user_id = u.user_id
            LEFT JOIN ' . TABLE_VERIFY_ACCOUNT . ' ON va.user_id = u.user_id
            WHERE u.user_email = ?
        ', [$userEmail]);
    }

    /**
     * Returns user by user hash
     *
     * @param  string $userHash User hash
     * 
     * @return array
     */
    public function byHash( string $userHash )
    {
        return $this->db->query('
            SELECT u.*, g.group_name, g.group_class, g.group_index, g.group_permission, ve.email_code, ve.email_code_sent
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_VERIFY_EMAIL . ' ON ve.user_id = u.user_id
            WHERE user_hash = ?
        ', [$userHash]) ?: [];
    }

    /**
     * Returns users unreaded conversations
     *
     * @param  int $userID User ID
     * 
     * @return array
     */
    public function unread( int $userID )
    {
        return array_column($this->db->query('
            SELECT conversation_id
            FROM ' . TABLE_USERS_UNREAD . '
            WHERE user_id = ?
        ', [$userID], ROWS), 'conversation_id');
    }
    
    /**
     * Returns all users
     *
     * @return array
     */
    public function all()
    {
        return $this->db->query('
            SELECT ' . $this->select->user(role: true) . ', group_name, group_index, user_reputation, user_registered
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_ROLES . ' ON ro.role_id = (
                SELECT role_id
                FROM ' . TABLE_ROLES . 'l
                WHERE FIND_IN_SET(rol.role_id, u.user_roles)
                ORDER BY rol.position_index DESC
                LIMIT 1
            )
            WHERE user_deleted = 0
            ORDER BY group_index DESC, user_registered ASC
            LIMIT ?, ?
        ', [$this->pagination['offset'], $this->pagination['max']], ROWS);
    }

    /**
     * Returns count of users
     * 
     * @return int
     */
    public function count()
    {
        return (int)$this->db->query('SELECT COUNT(*) as count FROM ' . TABLE_USERS . ' WHERE user_deleted = 0')['count'];
    }

    /**
     * Returns online users
     *
     * @return array
     */
    public function online()
    {
        return $this->db->query('
            SELECT ' . $this->select->user(role: true) . '
            FROM ' . TABLE_USERS. '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            LEFT JOIN ' . TABLE_ROLES . ' ON ro.role_id = (
                SELECT role_id
                FROM ' . TABLE_ROLES . 'l
                WHERE FIND_IN_SET(rol.role_id, u.user_roles)
                ORDER BY rol.position_index DESC
                LIMIT 1
            )
            WHERE user_last_activity > DATE_SUB(NOW(), INTERVAL 1 MINUTE) AND user_deleted = 0
        ', [], ROWS);
    }

    /**
     * Returns count of recent registered users
     *
     * @return int
     */
    public function recentCount()
    {
        return $this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_USERS . '
            WHERE user_registered > DATE_SUB(CURDATE(), INTERVAL 1 HOUR) AND user_deleted = 0
        ')['count'];
    }

    /**
     * Returns last registered users
     *
     * @param int $number Number of users
     * 
     * @return array
     */
    public function last( int $number = 5 )
    {
        return $this->db->query('
            SELECT ' . $this->select->user() . ', u.user_registered, g.group_name, g.group_index
            FROM ' . TABLE_USERS . '
            LEFT JOIN ' . TABLE_GROUPS . ' ON g.group_id = u.group_id
            WHERE user_deleted = 0
            ORDER BY user_registered DESC
            LIMIT ?
        ', [$number], ROWS);
    }

    /**
     * Returns user by forgot code
     *
     * @param string $forgotCode Forgot code
     * 
     * @return array
     */
    public function byForgotCode( string $forgotCode )
    {
        return $this->db->query('
            SELECT user_id
            FROM ' . TABLE_FORGOT . '
            WHERE forgot_code = ?
        ', [$forgotCode]);
    }

    /**
     * Returns user by account code
     *
     * @param string $accountCode Account code 
     * 
     * @return array
     */
    public function byAccountCode( string $accountCode )
    {
        return $this->db->query('
            SELECT user_id
            FROM ' . TABLE_VERIFY_ACCOUNT . '
            WHERE account_code = ?
        ', [$accountCode]);
    }

    /**
     * Returns user by email code
     *
     * @param string $emailCode Email code 
     * 
     * @return array
     */
    public function byEmailCode( string $emailCode )
    {
        return $this->db->query('
            SELECT user_id, user_email, email_code_sent
            FROM ' . TABLE_VERIFY_EMAIL . '
            WHERE email_code = ?
        ', [$emailCode]);
    }

    /**
     * Returns ID of all registered users
     *
     * @return array
     */
    public function getAllID()
    {
        return array_column($this->db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_deleted = 0', [], ROWS), 'user_id');
    }

    /**
     * Returns user activity
     *
     * @param  int $userID User ID
     * @param  bool $deleted If true - also deleted content will be returned
     * 
     * @return array
     */
    public function activity( int $userID, bool $deleted = false )
    {
        return $this->db->query('
            SELECT *
            FROM (
                SELECT "post" as type, post_id as item_id, p.topic_id as parent_id, topic_url as url, topic_text as text, post_created as created, topic_name as name, p.user_id, null as profile_user_id, null as profile_user_name, IFNULL(p.deleted_id, t.deleted_id) AS deleted_id,
                    (SELECT COUNT(*) FROM ' . TABLE_POSTS . '2 WHERE p2.post_id <= p.post_id AND p2.topic_id = p.topic_id ' . ($deleted === false ? 'AND p2.deleted_id IS NULL' : '') . ') AS position, 0 AS parent_position
                FROM ' . TABLE_POSTS . '
                LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
                LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_Id = t.forum_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = f.category_id 
                WHERE ((FIND_IN_SET("' . LOGGED_USER_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see))) ' . (!$deleted ? 'AND t.deleted_id IS NULL AND p.deleted_id IS NULL' : '' ) . '
                UNION 
                SELECT "topic" as type, topic_id as item_id, t.forum_id as parent_id, topic_url as url, topic_text as text, topic_created as created, topic_name as name, t.user_id, null as profile_user_id, null as profile_user_name, deleted_id, 1 AS position, 0 AS parent_position
                FROM ' . TABLE_TOPICS . '
                LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_Id = t.forum_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = f.category_id 
                WHERE ((FIND_IN_SET("' . LOGGED_USER_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see))) ' . (!$deleted ? 'AND t.deleted_id IS NULL' : '' ) . '
                UNION 
                SELECT "profilepost" as type, profile_post_id as item_id, null, u.user_name as url, profile_post_text as text, profile_post_created as created, u.user_name as name, pp.user_id, pp.profile_id as profile_user_id, u.user_name AS profile_user_name, pp.deleted_id,
                    (SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS . '2 WHERE pp2.profile_post_id >= pp.profile_post_id AND pp2.profile_id = pp.profile_id ' . ($deleted === false ? 'AND pp2.deleted_id IS NULL' : '') . ') AS position, 0 AS parent_position
                FROM ' . TABLE_PROFILE_POSTS . '
                LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = pp.profile_id
                ' . (!$deleted ? ' WHERE pp.deleted_id IS NULL' : '' ) . '
                UNION
                SELECT "profilepostcomment" as type, profile_post_comment_id as item_id, ppc.profile_post_id as parent_id, u.user_name AS url, profile_post_comment_text as text, profile_post_comment_created as created, u.user_name as name, ppc.user_id, pp.profile_id as profile_user_id, u.user_name AS profile_user_name, IFNULL(pp.deleted_id, ppc.deleted_id) AS deleted_id,
                (SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS_COMMENTS . '2 WHERE ppc2.profile_post_comment_id >= ppc.profile_post_comment_id AND ppc2.profile_id = ppc.profile_id ' . ($deleted === false ? 'AND ppc2.deleted_id IS NULL' : '') . ') AS position,
                (SELECT COUNT(*) FROM ' . TABLE_PROFILE_POSTS . '2 WHERE pp2.profile_post_id >= pp.profile_post_id AND pp2.profile_id = pp.profile_id ' . ($deleted === false ? 'AND pp.deleted_id IS NULL' : '') . ') AS parent_position
                FROM ' . TABLE_PROFILE_POSTS_COMMENTS . '
                LEFT JOIN ' . TABLE_PROFILE_POSTS . ' ON pp.profile_post_id = ppc.profile_post_id
                LEFT JOIN ' . TABLE_USERS . ' ON u.user_id = pp.profile_id
                ' . (!$deleted ? 'WHERE pp.deleted_id IS NULL AND ppc.deleted_id IS NULL' : '' ) . '
                UNION
                SELECT "topiclike" as type, t.topic_id as item_id, t.forum_id as parent_id, topic_url as url, null, like_created as created, topic_name as name, tl.user_id, null as profile_user_id, null as profile_user_name, t.deleted_id, 1 AS position, 0 AS parent_position
                FROM ' . TABLE_TOPICS_LIKES . '
                LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = tl.topic_id
                LEFT JOIN ' . TABLE_FORUMS . ' ON f.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = f.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = f.category_id 
                WHERE ((FIND_IN_SET("' . LOGGED_USER_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see))) ' . (!$deleted ? 'AND t.deleted_id IS NULL' : '' ) . '
                UNION
                SELECT "postlike" as type, p.post_id as item_id, p.topic_id as parent_id, topic_url as url, null, like_created as created, topic_name as name, pl.user_id, null as profile_user_id, null as profile_user_name, IFNULL(p.deleted_id, t.deleted_id) AS deleted_id,
                    (SELECT COUNT(*) FROM ' . TABLE_POSTS . '2 WHERE p2.post_id <= p.post_id AND p2.topic_id = p.topic_id ' . ($deleted === false ? 'AND p2.deleted_id IS NULL' : '') . ') AS position, 0 AS parent_position
                FROM ' . TABLE_POSTS_LIKES . '
                LEFT JOIN ' . TABLE_POSTS . ' ON p.post_id = pl.post_id
                LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id 
                WHERE ((FIND_IN_SET("' . LOGGED_USER_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see))) ' . (!$deleted ? 'AND t.deleted_id IS NULL AND p.deleted_id IS NULL' : '' ) . '
            ) a
            WHERE a.user_id = ?
            ORDER BY created DESC
            LIMIT ?, ?
        ', [$userID, $this->pagination['offset'], $this->pagination['max']], ROWS);
    }

    /**
     * Returns count of user activity
     *
     * @param int $userID User ID
     * @param  bool $deleted If true - also deleted content will be counted
     * 
     * @return int
     */
    public function activityCount( int $userID, bool $deleted = false )
    {
        $counts = $this->db->query('
            SELECT
            (
                SELECT COUNT(*)
                FROM phpcore_posts p
                LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id 
                WHERE p.user_id = ? AND ((FIND_IN_SET("' . LOGGED_USER_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see))) ' . (!$deleted ? 'AND t.deleted_id IS NULL AND p.deleted_id IS NULL' : '' ) . '
            ) as "0",
            (
                SELECT COUNT(*)
                FROM ' . TABLE_TOPICS . '
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id 
                WHERE user_id = ? AND ((FIND_IN_SET("' . LOGGED_USER_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see))) ' . (!$deleted ? 'AND t.deleted_id IS NULL' : '' ) . '
            ) as "1",
            (
                SELECT COUNT(*)
                FROM phpcore_profile_posts pp
                WHERE user_id = ? ' . (!$deleted ? 'AND pp.deleted_id IS NULL' : '' ) . '
            ) as "2",
            (
                SELECT COUNT(*)
                FROM phpcore_profile_posts_comments ppc
                LEFT JOIN phpcore_profile_posts pp ON pp.profile_post_id = ppc.profile_post_id
                WHERE ppc.user_id = ? ' . (!$deleted ? 'AND pp.deleted_id IS NULL AND ppc.deleted_id IS NULL' : '' ) . '
            ) as "3",
            (
                SELECT COUNT(*)
                FROM ' . TABLE_TOPICS_LIKES . '
                LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = tl.topic_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id 
                WHERE tl.user_id = ? AND ((FIND_IN_SET("' . LOGGED_USER_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see))) ' . (!$deleted ? 'AND t.deleted_id IS NULL' : '' ) . '
            ) as "4",
            (
                SELECT COUNT(*)
                FROM phpcore_posts_likes pl
                LEFT JOIN phpcore_posts p ON p.post_id = pl.post_id
                LEFT JOIN ' . TABLE_TOPICS . ' ON t.topic_id = p.topic_id
                LEFT JOIN ' . TABLE_FORUMS_PERMISSION . ' ON fp.forum_id = t.forum_id
                LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = t.category_id 
                WHERE pl.user_id = ? AND ((FIND_IN_SET("' . LOGGED_USER_ID . '", fp.permission_see) OR FIND_IN_SET("*", fp.permission_see)) AND (FIND_IN_SET("' . LOGGED_USER_ID . '", cp.permission_see) OR FIND_IN_SET("*", cp.permission_see))) ' . (!$deleted ? 'AND t.deleted_id IS NULL AND p.deleted_id IS NULL' : '' ) . '
            ) as "5"
        ', [$userID, $userID, $userID, $userID, $userID, $userID]);

        $number = 0;
        foreach ($counts as $key => $value)
        {
            $number += $value;
        }
        return $number;
    }
}