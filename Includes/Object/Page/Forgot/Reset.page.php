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
 * Reset
 */
class Reset extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Forgot/Change.phtml';

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
        if (!$system->get('site.allow_forgot_password') and !$system->get('registration.enabled'))
        {
            // Show 404 error page
            $this->error404();
        }

        // Get user by code
        $row = $db->select('app.user.byForgotCode()', $this->url->get('key')) or $this->error404();

        // Save data about user
        $data->set('data.forgot', $row);

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Forgot/Change.json');
        $form
            ->form('forgot')
                ->callOnSuccess($this, 'resetForgottenPassword');
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
    public function resetForgottenPassword( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check
        $check = new \App\Model\Check();

        // If passwords match
        if (!$check->passwordMatch($post->get('user_password_new'), $post->get('user_password_new_confirm')))
        {
            return;
        }

        // If password is valid
        if (!$check->password($post->get('user_password_new')))
        {
            return;
        }

        // Update password in database
        $db->update(TABLE_USERS, [
            'user_password' => password_hash($post->get('user_password_new'), PASSWORD_DEFAULT),
        ], $data->get('data.forgot.user_id'));

        // Delete record from "forgot password" table
        $db->delete(
            table: TABLE_FORGOT,
            id: $data->get('data.forgot.user_id')
        );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect user
        $data->set('data.redirect', INDEX);
    }
}