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

namespace Model\Database;

/**
 * QueryCompiler
 */
class QueryCompiler
{
    /**
     * @var array $tableKeys List of table keys
     */
    private array $tableKeys = [
        TABLE_BUTTONS => 'button_id',
        TABLE_BUTTONS_SUB => 'button_sub_id',
        TABLE_CATEGORIES => 'category_id',
        TABLE_CATEGORIES_PERMISSION_SEE => 'category_id',
        TABLE_FORGOT => 'user_id',
        TABLE_FORUMS => 'forum_id',
        TABLE_FORUM_ICONS => 'icon_id',
        TABLE_FORUMS_PERMISSION_SEE => 'forum_id',
        TABLE_FORUMS_PERMISSION_POST => 'forum_id',
        TABLE_FORUMS_PERMISSION_TOPIC => 'forum_id',
        TABLE_GROUPS => 'group_id',
        TABLE_LABELS => 'label_id',
        TABLE_LOG => 'log_id',
        TABLE_CONVERSATIONS => 'conversation_id',
        TABLE_CONVERSATIONS_MESSAGES => 'conversation_message_id',
        TABLE_PAGES => 'page_id',
        TABLE_POSTS => 'post_id',
        TABLE_POSTS_LIKES => 'post_id',
        TABLE_PROFILE_POSTS => 'profile_post_id',
        TABLE_PROFILE_POSTS_COMMENTS => 'profile_post_comment_id',
        TABLE_NOTIFICATIONS => 'notification_id',
        TABLE_NOTIFICATIONS => 'notification_id',
        TABLE_TOPICS => 'topic_id',
        TABLE_TOPICS_LABELS => 'label_id',
        TABLE_TOPICS_LIKES => 'topic_id',
        TABLE_USERS => 'user_id',
        TABLE_USERS_NOTIFICATIONS => 'user_notification_id',
        TABLE_USERS_UNREAD => 'user_id',
        TABLE_VERIFY_ACCOUNT => 'user_id',
        TABLE_VERIFY_EMAIL => 'user_id'
    ];

    /**
     * @var array $params Query parameters
     */
    private array $params = [];

    /**
     * @var string $table Table name
     */
    private string $table = '';

    /**
     * @var string $where Where statement
     */
    private string $where = '';

    /**
     * @var string $set Set statement
     */
    private string $set = '';

    /**
     * Constructor
     *
     * @param string $table Table name
     * @param array $query Array query
     * @param string $type Type of query
     * @param int $id Item ID, only for update query
     */
    public function __construct( string $table, array $query, string $type, int $id = null )
    {
        $this->table = $table;
        $this->type = $type;

        switch ($type) {

            case 'insert':
                
                $this->column =  implode(',', array_keys($query));
                $this->data = implode(',', array_fill(0, count($query), '?'));
                $this->params = array_values($query);

            break;

            case 'update':

                $i = 0;
                foreach ($query as $key => $value) {
                    if ($i !== 0) $this->set .= ', ';
                    $this->set .= $key . ' = ';

                    if (is_array($value)) {
                        switch ($value[0]) {
                            case PLUS:
                                $this->set .= $key . ' + 1 ';
                            break;
                            case MINUS:
                                $this->set .= $key . ' - 1 ';
                            break;
                        }
                    } else {
                        $this->set .= '? ';
                        array_push($this->params, $value);
                    }
                    $i++;
                }

                if ($id) {
                    $this->where = 'WHERE ' . $this->tableKeys[$table] . ' = ' . $id; 
                }
            
            break;

        }
    }
    
    /**
     * Returns query
     *
     * @return string
     */
    public function getQuery()
    {
        switch ($this->type) {
            case 'update':
                return 'UPDATE ' . $this->table . ' SET '. $this->set . ' ' . $this->where;
            break;
            case 'insert':
                return 'INSERT INTO ' . explode(' ', $this->table)[0] . ' (' . $this->column . ') VALUES (' . $this->data . ')';
            break;
        }
    }
    
    /**
     * Returns query parameters
     *
     * @return array
     */
    public function getParams()
    {
        return array_values($this->params);
    }
}