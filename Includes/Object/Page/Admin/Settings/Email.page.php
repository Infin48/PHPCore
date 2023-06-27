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

namespace App\Page\Admin\Settings;

/**
 * Email
 */
class Email extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.settings';

    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    protected function ajax( string $ajax )
    {
        return match($ajax)
        {
            'run/send-test-email' => 'sendTestEmail',

            default => ''
        };
    }

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');
        
        // Navbar
        $this->navbar->elm1('settings')->elm2('settings')->active()->elm3('email')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Settings/Email.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Settings/Email.json');
        $form
            ->form('email')
                ->callOnSuccess($this, 'editEmailSettings')
                ->data($system->get());
        $data->form = $form->getDataToGenerate();
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
    public function editEmailSettings( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Change system e-mail settings
        $db->table(TABLE_SETTINGS, [
            'email_prefix' => $post->get('email_prefix')
        ]);

        // Change smtp settings
        $db->table(TABLE_SETTINGS, [
            'email_smtp_host' => $post->get('email_smtp_host'),
            'email_smtp_port' => (int)$post->get('email_smtp_port'),
            'email_smtp_enabled' => (int)$post->get('email_smtp_enabled'),
            'email_smtp_username' => $post->get('email_smtp_username'),
            'email_smtp_password' => $post->get('email_smtp_password')
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
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
    public function sendTestEmail( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Get email of main administrator
        $email = $db->query('SELECT user_email FROM ' . TABLE_USERS . ' WHERE group_id = 1');

        // Send mail
        $mail = new \App\Model\Email\Test( system: $data->get('inst.system'), language: $data->get('inst.language') );
        $mail->mail->addAddress($email['user_email']);
        $mail->send();

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
    }
}