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

namespace Process\Admin\User;

use Model\File\File;

/**
 * Edit
 */
class Edit extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'user_name'     => [
                'type' => 'username',
                'required' => true
            ],
            'user_text' => [
                'type' => 'text'
            ],
            'user_email'    => [
                'type' => 'email',
                'required' => true
            ],
            'user_password' => [
                'type' => 'password'
            ],
            'group_id'      => [
                'type' => 'number',
                'block' => '\Block\Group.getLessID',
                'required' => true
            ],
            'delete_signature' => [
                'type' => 'checkbox'
            ],
            'delete_profile_image' => [
                'type' => 'checkbox'
            ],
            'delete_header_image' => [
                'type' => 'checkbox'
            ]
        ],
        'data' => [
            'user_id'
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
        // FILE MODEL
        $file = new File();

        if ($this->db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_name = ? and user_id <> ?', [$this->data->get('user_name'), $this->data->get('user_id')])) {
            throw new \Exception\Notice('user_name_exist');
        }

        if ($this->db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_email = ? and user_id <> ?', [$this->data->get('user_email'), $this->data->get('user_id')])) {
            throw new \Exception\Notice('user_email_exist');
        }

        if ($this->data->get('user_password')) {

            if ($this->check->password($this->data->get('user_password'))) {
                
                $this->db->update(TABLE_USERS, [
                    'user_password' => password_hash($this->data->get('user_password'), PASSWORD_DEFAULT)
                ], $this->data->get('user_id'));
            }
        }

        if ($this->data->is('delete_signature')) {
            $this->db->query('UPDATE ' . TABLE_USERS . ' SET user_signature = "" WHERE user_id = ?', [$this->data->get('user_id')]);
        }

        if ($this->data->is('delete_profile_image')) {
            $file->deleteImage('/User/' . $this->data->get('user_id') . '/Profile');
            $this->db->query('UPDATE ' . TABLE_USERS . ' SET user_profile_image = ? WHERE user_id = ?', [getProfileImageColor(), $this->data->get('user_id')]);
        }

        if ($this->data->is('delete_header_image')) {
            $file->deleteImage('/User/' . $this->data->get('user_id') . '/Header');
            $this->db->query('UPDATE ' . TABLE_USERS . ' SET user_header_image = "" WHERE user_id = ?', [$this->data->get('user_id')]);
        }

        // DELETE EMAIL VERIFICATION TO THIS EMAIL
        $this->db->query('DELETE FROM ' . explode(' ', TABLE_VERIFY_EMAIL)[0] . ' WHERE user_email = ?', [$this->data->get('user_email')]);

        $this->db->update(TABLE_USERS, [
            'group_id'          => $this->data->get('group_id'),
            'user_name'         => $this->data->get('user_name'),
            'user_text'         => $this->data->get('user_text'),
            'user_email'        => $this->data->get('user_email')
        ], $this->data->get('user_id'));

        // ADD RECORD TO LOG
        $this->log($this->data->get('user_name'));
    }
}