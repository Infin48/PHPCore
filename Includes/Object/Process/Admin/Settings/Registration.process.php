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

use Model\File\Text;

/**
 * Registration
 */
class Registration extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'registration_enabled'      => [
                'type' => 'checkbox'
            ],
            'registration_key_site'     => [
                'type' => 'text'
            ],
            'registration_key_secret'   => [
                'type' => 'text'
            ],
            'registration_terms'   => [
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
        if ($this->data->is('registration_enabled')) {

            if (!$this->data->get('registration_key_site') or !$this->data->get('registration_key_secret')) {
                return true;
            }

            $text = new Text('/Assets/reCAPTCHA/reCAPTCHA.org.min.js');
            $text->set('site_key', $this->data->get('registration_key_site'));
            $text->save('/Assets/reCAPTCHA/reCAPTCHA.min.js');
        }

        $this->db->table(TABLE_SETTINGS, [
            'registration.terms' => $this->data->get('registration_terms'),
            'registration.enabled' => (int)$this->data->get('registration_enabled'),
            'registration.key_site' => $this->data->get('registration_key_site'),
            'registration.key_secret' => $this->data->get('registration_key_secret')
        ]);

        // UPDATE SESSIONS
        $this->db->table(TABLE_SETTINGS, [
            'session' => RAND,
            'session.scripts' => RAND
        ]);

        // ADD RECORD TO LOG
        $this->log();
    }
}