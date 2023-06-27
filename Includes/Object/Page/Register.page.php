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
 * Register
 */
class Register extends Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Register.phtml';

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

        // If registration isn't allowed
        if ($system->get('registration_enabled') == 0)
        {
            // Show error page
            $this->error404();
        }

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Register.json');
        $form->form('register')->callOnSuccess($this, 'registerUser');
        $data->form = $form->getDataToGenerate();

        // data.link.login - Link to login page for already logged users
        $data->set('data.link.login', '<a href="' . $this->url->build('/login/') . '">' . $language->get('L_REGISTER.L_ALREADY') . '</a>');
        
        // data.link.terms - Link to terms
        $data->set('data.link.terms', '<a href="' . $this->url->build('/terms/') . '">' . $language->get('L_REGISTER.L_TERMS') . '</a>');
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
    protected function registerUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // System
        $system = $data->get('inst.system');

        // Check
        $check = new \App\Model\Check();

        // Check password
        if (!$check->passwordMatch($post->get('user_password'), $post->get('user_password_confirm')))
        {
            return false;
        }

        // Check e-mail
        if (!$check->email($post->get('user_email')))
        {
            return false;
        }

        $res = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, stream_context_create([
            'http' => [
                'header'  => 'Content-type: application/x-www-form-urlencoded\r\n',
                'method'  => 'POST',
                'content' => http_build_query([
                    'secret' => $system->get('registration_key_secret'),
                    'response' => $post->get('token'),
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ])
            ]
        ])), true);

        if ($res['success'] != true)
        {
            throw new \App\Exception\Notice('recaptcha');
        }

        // If exists user with entered username
        if (!empty($db->select('app.user.byName()', $post->get('user_name'))))
        {
            throw new \App\Exception\Notice('user_name_exist');
        }

        // If exists user with entered email
        if (!empty($db->select('app.user.byEmail()', $post->get('user_email'))))
        { 
            throw new \App\Exception\Notice('user_email_exist');
        }

        // Add new user
        $db->insert(TABLE_USERS, [
            'group_id' => $system->get('default_group'),
            'user_name' => $post->get('user_name'),
            'user_email' => $post->get('user_email'),
            'user_password' => password_hash($post->get('user_password'), PASSWORD_DEFAULT),
            'user_profile_image' => getProfileImageColor()
        ]);

        // Delete email verification to registered email
        $db->delete(table: TABLE_VERIFY_EMAIL, key: 'user_email', id: $post->get('user_email'));
        
        if ($system->get('registration_verify'))
        {
            $code = md5(mt_rand());

            // Add user to 'phpcore_verify_account'
            $db->insert(TABLE_VERIFY_ACCOUNT, [
                'account_code' => $code,
                'user_id' => $db->lastInsertId()
            ]);

            // Send an email to verify account
            $mail = new \App\Model\Email\Register( system: $data->get('inst.system'), language: $data->get('inst.language') );
            $mail->mail->addAddress($post->get('user_email'), $post->get('user_name'));
            $mail->assign(['code' => $code]);
            $mail->send();

            // Show success message
            $data->set('data.message.success', __FUNCTION__);
            
            // Redirect
            $data->set('data.redirect', INDEX);

            return;
        }
        
        // Show success message
        $data->set('data.message.success', __FUNCTION__ . 'WithoutEmailVerification');
        
        // Redirect
        $data->set('data.redirect', INDEX);
    }
}