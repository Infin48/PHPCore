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

namespace Block;

/**
 * Conversation
 */
class Conversation extends Block
{    
    /**
     * Returns conversation
     *
     * @param  int $conversationID Conversation ID
     * 
     * @return array
     */
    public function get( int $conversationID )
    {
        return $this->db->query('
            SELECT c.*, ' . $this->select->user() . ', user_last_activity, user_signature, group_name, user_topics, user_posts, user_reputation
            FROM ' . TABLE_CONVERSATIONS . '
            ' . $this->join->user('c.user_id'). '
            LEFT JOIN ' . TABLE_CONVERSATIONS_RECIPIENTS . ' ON cr.conversation_id = c.conversation_id
            WHERE c.conversation_id = ? AND cr.user_id = ?
            GROUP BY c.conversation_id',
        [$conversationID, LOGGED_USER_ID]);
    }
    
    /**
     * Returns all conversations
     *
     * @return array
     */
    public function getAll()
    {
        return $this->db->query('
            SELECT c.*, cm.conversation_message_created, cm.conversation_message_id, ' . $this->select->user() . ',  u2.user_id AS message_user_id, u2.user_name AS message_user_name, u2.user_profile_image AS message_user_profile_image, g2.group_class_name AS message_group_class_name, u2.user_deleted AS message_user_deleted, ( SELECT COUNT(*) FROM ' . TABLE_CONVERSATIONS_RECIPIENTS . '2 WHERE cr2.conversation_id = c.conversation_id ) AS recipients
            FROM ' . TABLE_CONVERSATIONS . '
            ' . $this->join->user('c.user_id'). '
            LEFT JOIN ' . TABLE_CONVERSATIONS_RECIPIENTS . ' ON cr.conversation_id = c.conversation_id AND cr.user_id = ?
            LEFT JOIN ' . TABLE_CONVERSATIONS_MESSAGES . ' ON cm.conversation_message_id = ( SELECT MAX(conversation_message_id) FROM ' . TABLE_CONVERSATIONS_MESSAGES . '2 WHERE cm2.conversation_id = c.conversation_id )
            LEFT JOIN ' . TABLE_USERS . '2 ON u2.user_id = cm.user_id
            LEFT JOIN ' . TABLE_GROUPS . '2 ON g2.group_id = u2.group_id
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
    public function getAllCount()
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
     * @param  int $conversationID Conversation ID
     * 
     * @return array
     */
    public function getRecipients( int $conversationID )
    {
        return $this->db->query('
            SELECT ' . $this->select->user() . ', group_name
            FROM ' . TABLE_CONVERSATIONS_RECIPIENTS . '
            ' . $this->join->user('cr.user_id'). '
            WHERE cr.conversation_id = ?',
        [$conversationID], ROWS);
    }
}