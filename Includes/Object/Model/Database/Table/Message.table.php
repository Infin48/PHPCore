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
 * Message
 */
class Message extends Table
{
    /**
     * Returns conversation message
     *
     * @param  int $ID Conversation message ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        return $this->db->query('
            SELECT cm.*, c.conversation_name, ' . $this->select->user(role: true) . ', user_signature
            FROM ' . TABLE_CONVERSATIONS_MESSAGES . '
            ' . $this->join->user(on: 'cm.user_id', role: true). '
            LEFT JOIN ' . TABLE_CONVERSATIONS . ' ON c.conversation_id = cm.conversation_id
            WHERE conversation_message_id = ?', [$ID]
        );
    }
    
    /**
     * Returns conversation messages from conversation
     *
     * @param  int $ID Conversation ID
     * 
     * @return array
     */
    public function parent( int $ID )
    {
        return $this->db->query('
            SELECT cm.*, c.conversation_name, ' . $this->select->user(role: true) . ', user_topics, user_posts, user_reputation
            FROM ' . TABLE_CONVERSATIONS_MESSAGES . '
            LEFT JOIN ' . TABLE_CONVERSATIONS . ' ON c.conversation_id = cm.conversation_id
            ' . $this->join->user(on: 'cm.user_id', role: true). '
            WHERE cm.conversation_id = ?
            ORDER BY conversation_message_id ASC
            LIMIT ?, ?',
        [$ID, $this->pagination['offset'], $this->pagination['max']], ROWS);
    }
}