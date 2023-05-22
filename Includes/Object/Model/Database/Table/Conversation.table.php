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
 * Conversation
 */
class Conversation extends Table
{    
    /**
     * Returns conversation
     *
     * @param  int $ID Conversation ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('
            SELECT c.*, ' . $this->select->user( role: true ) . ', group_name, user_topics, user_posts, user_reputation
            FROM ' . TABLE_CONVERSATIONS . '
            ' . $this->join->user(on: 'c.user_id', role: true). '
            LEFT JOIN ' . TABLE_CONVERSATIONS_RECIPIENTS . ' ON cr.conversation_id = c.conversation_id
            WHERE c.conversation_id = ? AND cr.user_id = ?
            GROUP BY c.conversation_id',
        [$ID, LOGGED_USER_ID]);
    }
    
    /**
     * Returns all conversations
     *
     * @return array
     */
    public function all()
    {
        return $this->db->query('
            SELECT c.*, cm.conversation_message_created, cm.conversation_message_id, ' . $this->select->user(role: true) . ',  u2.user_id AS message_user_id, u2.user_name AS message_user_name, u2.user_profile_image AS message_user_profile_image, g2.group_class AS message_group_class, u2.user_deleted AS message_user_deleted, ( SELECT COUNT(*) FROM ' . TABLE_CONVERSATIONS_RECIPIENTS . '2 WHERE cr2.conversation_id = c.conversation_id ) AS recipients,
                ro2.role_name as message_role_name, ro2.role_class AS message_role_class, ro2.role_color AS message_role_color, ro2.role_icon AS message_role_icon
            FROM ' . TABLE_CONVERSATIONS . '
            ' . $this->join->user(on: 'c.user_id', role: true). '
            LEFT JOIN ' . TABLE_CONVERSATIONS_RECIPIENTS . ' ON cr.conversation_id = c.conversation_id AND cr.user_id = ?
            LEFT JOIN ' . TABLE_CONVERSATIONS_MESSAGES . ' ON cm.conversation_message_id = ( SELECT MAX(conversation_message_id) FROM ' . TABLE_CONVERSATIONS_MESSAGES . '2 WHERE cm2.conversation_id = c.conversation_id )
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id = cm.user_id
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_id = u2.group_id
            LEFT JOIN ' . TABLE_ROLES . '2 ON ro2.role_id = (
                SELECT role_id
                FROM ' . TABLE_ROLES . 'l2
                WHERE FIND_IN_SET(rol2.role_id, u2.user_roles)
                ORDER BY rol2.position_index DESC
                LIMIT 1
            )
            WHERE cr.conversation_id IS NOT NULL
            ORDER BY CASE WHEN cm.conversation_message_id IS NOT NULL THEN cm.conversation_message_created ELSE c.conversation_created END DESC
            LIMIT ?, ?
        ',[LOGGED_USER_ID, $this->pagination['offset'], $this->pagination['max']], ROWS);
    }
    
    /**
     * Returns count of conversations
     *
     * @return int
     */
    public function count()
    {
        return (int)$this->db->query('
            SELECT COUNT(*) AS count
            FROM ' . TABLE_CONVERSATIONS . '
            LEFT JOIN ' . TABLE_CONVERSATIONS_RECIPIENTS . ' ON cr.conversation_id = c.conversation_id AND cr.user_id = ?
            WHERE cr.conversation_id IS NOT NULL
            ORDER BY c.conversation_id DESC
        ',[LOGGED_USER_ID])['count'] ?? 0;
    }
    
    /**
     * Returns recipients of conversation
     *
     * @param  int $ID Conversation ID
     * 
     * @return array
     */
    public function recipients( int $ID )
    {
        return $this->db->query('
            SELECT ' . $this->select->user(role: true) . ', group_name
            FROM ' . TABLE_CONVERSATIONS_RECIPIENTS . '
            ' . $this->join->user(on: 'cr.user_id', role: true). '
            WHERE cr.conversation_id = ?',
        [$ID], ROWS);
    }
}