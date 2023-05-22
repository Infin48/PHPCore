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

namespace App\Page;

/**
 * Verify
 */
class Verify extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        if (!$this->url->get('code') or !$this->url->get('type'))
        {
            $this->error404();
        }
        
        $method = match ($this->url->get('type'))
        {
            'email' => 'verifyEmail',
            'account' => 'verifyAccount',

            default => $this->error404()
        };
        
        $this->runProcess($method);
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function verifyEmail( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $user = $db->select('app.user.byEmailCode()', $this->url->get('code'));

        if (!$user)
        {
            return false;
        }

        // Delete record
        $db->delete(
            table: TABLE_VERIFY_EMAIL,
            key: 'user_email',
            id: $user['user_email']
        );

        // Update user informations
        $db->update(TABLE_USERS, [
            'user_email' => $user['user_email']
        ], $user['user_id']);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', INDEX);
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function verifyAccount( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $user = $db->select('app.user.byAccountCode()', $this->url->get('code'));

        if (!$user)
        {
            return false;
        }
        
        $db->delete(
            table: TABLE_VERIFY_ACCOUNT,
            id: $user['user_id']
        );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect user
        $data->set('data.redirect', INDEX);
    }
}