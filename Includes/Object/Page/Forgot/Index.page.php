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

namespace App\Page\Forgot;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Forgot/Send.phtml';

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
        
        // If is not allowed to reset forgot password
        if (!$system->get('site_allow_forgot_password') and !$system->get('registration_enabled'))
        {
            // Show 404 error page
            $this->error404();
        }

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Forgot/Send.json');
        $form
            ->form('forgot')
                ->callOnSuccess($this, 'sendLinkToResetPassword');
        $data->form = $form->getDataToGenerate();

        $data->set('data.link.login', '<a href="' . $this->url->build('/login/') . '">' . $language->get('L_FORGOT.L_BACK_TO_LOGIN') . '</a>');
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
    public function sendLinkToResetPassword( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $user = $db->select('app.user.byEmail()', $post->get('user_email'));

        if (!$user)
        {
            throw new \App\Exception\Notice('user_email_does_not_exist');
        }

        // If user account is not ativated
        if ($user['account_code'])
        {
            throw new \App\Exception\Notice('account_not_activated');
        }

        // If e-mail about forgotten password was recently already sent
        if (time() < strtotime('+300 seconds', strtotime($user['forgot_code_sent'])))
        {
            throw new \App\Exception\Notice('forgot_already_sent');
        }

        // Generate code
        $code = !$user['forgot_code'] ? substr(md5(RAND), 0, 15) : $user['forgot_code'];

        if (!$user['forgot_code'])
        {
            // Insert code to database
            $db->insert(TABLE_FORGOT, [
                'user_id' => $user['user_id'],
                'forgot_code' => $code
            ]);
        }

        // Update time
        $db->update(TABLE_FORGOT, [
            'forgot_code_sent' => DATE_DATABASE
        ], $user['user_id']);

        // Send mail with code
        $mail = new \App\Model\Email\Forgot( system: $data->get('inst.system'), language: $data->get('inst.language') );
        $mail->mail->addAddress($post->get('user_email'), $user['user_name']);
        $mail->assign(['code' => $code]);
        $mail->send();

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', INDEX);
    }
}