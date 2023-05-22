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

namespace App\Model\Database;

/**
 * QueryCompiler
 */
class QueryCompiler
{
    /**
     * @var array $tableKeys List of table keys
     */
    public static array $tableKeys = [
        TABLE_ARTICLES => 'article_id',
        TABLE_BUTTONS => 'button_id',
        TABLE_BUTTONS_SUB => 'button_sub_id',
        TABLE_CATEGORIES => 'category_id',
        TABLE_CATEGORIES_PERMISSION => 'category_id',
        TABLE_FORGOT => 'user_id',
        TABLE_FORUMS => 'forum_id',
        TABLE_FORUMS_PERMISSION => 'forum_id',
        TABLE_GROUPS => 'group_id',
        TABLE_LABELS => 'label_id',
        TABLE_LOG => 'log_id',
        TABLE_SIDEBAR => 'sidebar_id',
        TABLE_SETTINGS_URL => 'settings_url_id',
        TABLE_CONVERSATIONS => 'conversation_id',
        TABLE_CONVERSATIONS_MESSAGES => 'conversation_message_id',
        TABLE_PAGES => 'page_id',
        TABLE_PLUGINS => 'plugin_id',
        TABLE_POSTS => 'post_id',
        TABLE_ROLES => 'role_id',
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
     * @var string $table Table alias
     */
    private string $alias = '';

    /**
     * @var string $where Where statement
     */
    private string $where = '';

    /**
     * @var string $set Set statement
     */
    private string $set = '';

    /**
     * @var int $flag Additional flag
     */
    private int $flag = 0;

    /**
     * Compiles query
     *
     * @param string $table Table name
     * @param array|string $query Array query
     * @param string $type Type of query
     * @param int|string $id Item ID, only for update query
     * @param int $flag Additional flag
     * 
     * @return null
     */
    public function compile( string $table, string $type, array|string $query = null, int|string $id = null, int $flag = null )
    {
        list($this->table, $this->alias) = explode(' ', $table);
        $this->flag = $flag ?? 0;
        $this->type = $type;
        $this->set = '';
        $this->where = '';
        $this->params = [];

        switch ($type) {

            case 'delete':
            
                if ($id)
                {
                    $key = self::$tableKeys[$table];
                    if ($query)
                    {
                        $key = $query;
                    }

                    $this->where = 'WHERE ' . $key . ' = ?';
                    $this->params = [$id];
                }
            
            break;

            case 'insert':

                
                $this->column =  '`' . implode('`,`', array_keys($query)) . '`';
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
                    $this->where = 'WHERE ' . self::$tableKeys[$table] . ' = ' . $id; 
                }
            
            break;

        }
    }

    /**
     * Retnrs keys according to table
     * 
     * @return array
     */
    public function getKeys()
    {
        return self::$tableKeys;
    }

    /**
     * Adds table and key
     *
     * @param string $table Table name
     * @param string $table primary key
     * 
     * @return null
     */
    public static function addTable( string $table, string $key )
    {
        self::$tableKeys[$table] = $key;
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
                return 'INSERT ' . ($this->flag & Q_IGNORE ? 'IGNORE ' : '' ) . 'INTO ' . $this->table . ' (' . $this->column . ') VALUES (' . $this->data . ')' . ($this->flag & Q_DUPLICATE ? ' ON DUPLICATE KEY UPDATE `key` = ?' : '' );
            break;

            case 'delete':
                return 'DELETE FROM ' . $this->table . ' ' . $this->where;
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