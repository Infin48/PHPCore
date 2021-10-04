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

namespace Process\Report;

/**
 * Send
 */
class Send extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'user_id',
            'report_id',
            'report_type',
            'report_type_id',
            'report_reason_text'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        if ($this->data->get('report_id')) {

            $this->db->query('
                UPDATE ' . TABLE_REPORTS . '
                SET report_status = 0
                WHERE report_id = ?
            ', [$this->data->get('report_id')]);

            self::$id = $this->data->get('report_id');
        } else {

            $this->db->insert(TABLE_REPORTS, [
                'report_type' => $this->data->get('report_type'),
                'report_type_id' => $this->data->get('report_type_id'),
                'report_type_user_id' => $this->data->get('user_id')
            ]);
            self::$id = $this->db->lastInsertId();

            switch ($this->data->get('report_type')) {

                case 'Post':
                    $this->db->query('UPDATE ' . TABLE_POSTS . ' SET report_id = ? WHERE p.post_id = ?', [self::$id, $this->data->get('report_type_id')]);
                break;

                case 'Topic':
                    $this->db->query('UPDATE ' . TABLE_TOPICS . ' SET report_id = ? WHERE t.topic_id = ?', [self::$id, $this->data->get('report_type_id')]);
                break;

                case 'ProfilePost':
                    $this->db->query('UPDATE ' . TABLE_PROFILE_POSTS . ' SET report_id = ? WHERE pp.profile_post_id = ?', [self::$id, $this->data->get('report_type_id')]);
                break;

                case 'ProfilePostComment':
                    $this->db->query('UPDATE ' . TABLE_PROFILE_POSTS_COMMENTS . ' SET report_id = ? WHERE ppc.profile_post_comment_id = ?', [self::$id, $this->data->get('report_type_id')]);
                break;
            }
        }

        $this->db->insert(TABLE_REPORTS_REASONS, [
            'user_id' => LOGGED_USER_ID,
            'report_id' => self::$id,
            'report_reason_text' => $this->data->get('report_reason_text')
        ]);
    }
}