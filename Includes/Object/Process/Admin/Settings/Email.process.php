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

namespace Process\Admin\Settings;

/**
 * Email
 */
class Email extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'email_prefix' => [
                'type' => 'text',
                'required' => true,
                'function' => 'ctype_alpha'
            ],
            'email_smtp_enabled' => [
                'type' => 'radio'
            ],
            'email_smtp_host'     => [
                'type' => 'text'
            ],
            'email_smtp_username' => [
                'type' => 'text'
            ],
            'email_smtp_password' => [
                'type' => 'text'
            ],
            'email_smtp_port'     => [
                'type' => 'text'
            ]
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
        $settings = $this->system->settings->get();
        $settings['email.prefix'] = $this->data->get('email_prefix');

        $settings['email.smtp_enabled'] = (int)$this->data->is('email_smtp_enabled');

        if ($this->data->is('email_smtp_enabled')) {
            $settings['email.smtp_host'] = $this->data->get('email_smtp_host');
            $settings['email.smtp_username'] = $this->data->get('email_smtp_username');
            $settings['email.smtp_password'] = $this->data->get('email_smtp_password');
            $settings['email.smtp_port'] = $this->data->get('email_smtp_port');
        }

        if (empty($this->data->get('email_smtp_host')) or empty($this->data->get('email_smtp_username'))) {
            $settings['email.smtp_enabled'] = 0;
        }

        $this->system->settings->set($settings);

        // ADD RECORD TO LOG
        $this->log();
    }
}