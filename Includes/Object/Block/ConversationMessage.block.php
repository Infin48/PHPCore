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
 * ConversationMessage
 */
class ConversationMessage extends Block
{
    /**
     * Returns conversation message
     *
     * @param  int $conversationMessageID Conversation message ID
     * 
     * @return array
     */
    public function get( int $conversationMessageID )
    {
        return $this->db->query('
            SELECT cm.*, c.conversation_name
            FROM ' . TABLE_CONVERSATIONS_MESSAGES . '
            LEFT JOIN ' . TABLE_CONVERSATIONS . ' ON c.conversation_id = cm.conversation_id
            WHERE conversation_message_id = ?', [$conversationMessageID]
        );
    }
    
    /**
     * Returns conversation messages from conversation
     *
     * @param  int $conversationID Conversation ID
     * 
     * @return array
     */
    public function getParent( int $conversationID )
    {
        return $this->db->query('
            SELECT cm.*, c.conversation_name, ' . $this->select->user() . ', group_name, user_last_activity, user_signature, user_topics, user_posts, user_reputation
            FROM ' . TABLE_CONVERSATIONS_MESSAGES . '
            LEFT JOIN ' . TABLE_CONVERSATIONS . ' ON c.conversation_id = cm.conversation_id
            ' . $this->join->user('cm.user_id'). '
            WHERE cm.conversation_id = ?
            ORDER BY conversation_message_id ASC
            LIMIT ?, ?',
        [$conversationID, $this->pagination['offset'], $this->pagination['max']], ROWS);
    }
}