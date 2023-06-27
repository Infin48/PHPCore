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

namespace App\Page\User;

/**
 * Account
 */
class Account extends \App\Page\Page
{
    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/User/Account.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/User/Index.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Account.json');
        $form
            ->form('account')
                ->data($user->get())
                ->callOnSuccess($this, 'editAccount');
        $data->form = $form->getDataToGenerate();

        // Sidebar
        $sidebar = new \App\Visualization\Sidebar\Sidebar('Root/Sidebar:/Formats/User.json');
        $sidebar
            // Set sidebar to show on left side
            ->left()
            // Set small version
            ->small()
            ->elm1('basic')
                // Select current button with link to page where we are right now 
                ->elm2('account')->select()
                // Set position to settings button
                ->elm2('settings', function ($sidebar) use ($system)
                {
                    // If is enabled blog mode
                    if ($system->get('site_mode') == 'blog')
                    {
                        // And also profiles are disabled
                        if ($system->get('site_mode_blog_profiles') == 0)
                        {
                            // Hide this ubtton in sidebar
                            $sidebar->hide();
                        }
                    }
            })
            ->elm1('basic')
                // Set position to signature button
                ->elm2('signature', function ($sidebar) use ($system)
                {
                    // If is enabled blog mode
                    if ($system->get('site_mode') != 'blog')
                    {
                        // Show this button in sidebar
                        $sidebar->show();
                    }
                })
            // Set position to conversation sidebar
            ->elm1('conversation', function ($sidebar) use ($system)
            {
                // If is enabled blog mode
                if ($system->get('site_mode') != 'blog')
                {
                    // Show this buttoin in sidebar
                    $sidebar->show();
                }
            });

        // Finish sidebar and get ready to generate
        $data->sidebar = $sidebar->getDataToGenerate();
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
    public function editAccount( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // User
        $user = $data->get('inst.user');

        // Check
        $check = new \App\Model\Check();

        // User with entered name already exist
        if ($db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_name = ? and user_id <> ?', [$post->get('user_name'), LOGGED_USER_ID]))
        {
            throw new \App\Exception\Notice('user_name_exist');
        }

        // User with entered e-mail already exist
        if ($db->query('SELECT user_id FROM ' . TABLE_USERS . ' WHERE user_email = ? and user_id <> ?', [$post->get('user_email'), LOGGED_USER_ID]))
        {
            throw new \App\Exception\Notice('user_email_exist');
        }

        // User doesnt confired new password
        if ($post->get('user_password_new') and !$post->get('user_password_new_confirm'))
        {
            throw new \App\Exception\Notice('user_password_new_confirm');
        }

        // Passwords match
        if (!$check->passwordMatch($post->get('user_password'), $user->get('user_password'), 'user_password_wrong'))
        {
            return false;
        }

        // Check e-mail
        if (!$check->email($post->get('user_email')))
        {
            return false;
        }
        
        // User changed his e-mail 
        if ($user->get('user_email') != $post->get('user_email'))
        {
            // If e-mail was recently changed
            if (time() < strtotime('+600 seconds', strtotime($user->get('email_code_sent'))))
            {
                throw new \App\Exception\Notice('email_verify_already_sent');
            }

            // Generate code
            $code = substr(md5(RAND), 0, 15);

            if ($user->get('email_code')) {

                $code = $user->get('email_code');

                $db->update(TABLE_VERIFY_EMAIL, [
                    'user_email' => $post->get('user_email'),
                    'email_code_sent' => DATE_DATABASE,
                    'email_code'    => $code
                ], LOGGED_USER_ID);
            } else {

                $db->insert(TABLE_VERIFY_EMAIL, [
                    'user_id'       => LOGGED_USER_ID,
                    'user_email'    => $post->get('user_email'),
                    'email_code'    => $code
                ]);
            }

            // Send mail with code
            $mail = new \App\Model\Email\Verify( system: $data->get('inst.system'), language: $data->get('inst.language') );
            $mail->mail->addAddress($post->get('user_email'), $post->get('user_name'));
            $mail->assign(['code' => $code]);
            $mail->send();
        }

        if ($post->get('user_password_new') and $post->get('user_password_new_confirm')) {

            if ($check->passwordMatch($post->get('user_password_new'), $post->get('user_password_new_confirm'))) {
                
                // Update user informations
                $db->update(TABLE_USERS, [
                    'user_password' => password_hash($post->get('user_password_new'), PASSWORD_DEFAULT),
                ], LOGGED_USER_ID);
            }
        }

        // Update user informations
        $db->update(TABLE_USERS, [
            'user_name' => $post->get('user_name')
        ], LOGGED_USER_ID);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
    }
}