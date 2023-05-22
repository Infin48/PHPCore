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

use \App\Model\Cookie;
use \App\Model\Session;

/**
 * Login
 */
class Login extends Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Login.phtml';

    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 2;

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // Language
        $language = $data->get('inst.language');

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Login.json');
        $form->form('login')->callOnSuccess($this, 'loginUser');
        $data->form = $form->getDataToGenerate();

        // If is allowed to reset password using email
        if ($system->get('site.allow_forgot_password') == 1 or $system->get('registration.enabled'))
        {
            // data.link.forgot - Link to reset password
            $data->set('data.link.forgot', '<a href="' . $this->url->build('/forgot/') . '">' . $language->get('L_FORGOT.L_FORGOT') . '</a>');
        }

        // If registration is allowed
        if ($system->get('registration.enabled') == 1)
        {
            // data.link.register - Link to register page
            $data->set('data.link.register', '<a href="' . $this->url->build('/register/')  . '">' . $language->get('L_REGISTER.L_NO') . '</a>');
        }
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
    public function loginUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $user = $db->select('app.user.byName()', $post->get('user_name'));
        
        // If enetered password doesnt match with password from database
        if (!$user or !password_verify($post->get('user_password'), $user['user_password']))
        {
            throw new \App\Exception\Notice('login_incorrect');
        }
        
        // If account is not activated
        if ($user['account_code'])
        {
            // If activation e-mail was recently already sent
            if (time() < strtotime('+300 seconds', strtotime($user['account_code_sent'])))
            {
                throw new \App\Exception\Notice('account_verify_already_sent');
            }
            
            // Send an email to verify account
            $mail = new \App\Model\Email\Register( system: $data->get('inst.system'), language: $data->get('inst.language') );
            $mail->mail->addAddress($user['user_email'], $user['user_name']);
            $mail->assign(['code' => $user['account_code']]);
            $mail->send();

            // Update time
            $db->update(TABLE_VERIFY_ACCOUNT, [
                'account_code_sent' => DATE_DATABASE
            ], $user['user_id']);

            throw new \App\Exception\Notice('account_not_activated_send');
        }

        // Update user hash and last activity
        $db->update(TABLE_USERS, [
            'user_hash' => $token = md5(uniqid(mt_rand(), true)),
            'user_last_activity' => DATE_DATABASE
        ], $user['user_id']);

        // Delete record in 'phpcore_forgot_password'
        $db->delete(table: TABLE_FORGOT, id: $user['user_id']);

        Session::delete('token');
        Cookie::delete('token');

        Session::put('token', $token);

        if ($post->get('remember'))
        {
            Cookie::put('token', $token, 365 * 24 * 3600);
        } 

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect
        $data->set('data.redirect', INDEX);
    }
}