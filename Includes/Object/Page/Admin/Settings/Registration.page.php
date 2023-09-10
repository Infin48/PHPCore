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
 * Registration
 */
class Registration extends \App\Page\Page
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
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // If forum in not enabled
		if (!in_array($system->get('site_mode'), ['forum', 'blog_with_forum']))
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('settings')->elm2('settings')->active()->elm3('registration')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Settings/Registration.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Settings/Registration.json');
        $form
            ->form('registration')
                ->callOnSuccess($this, 'editRegistrationSettings')
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
    public function editRegistrationSettings( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // If registration is enabled
        if ($post->get('registration_enabled'))
        {
            // If is not enetered one of key
            if (!$post->get('registration_key_site') or !$post->get('registration_key_secret'))
            {
                return true;
            }

            // Set key to recaptcha file
            $text = new \App\Model\File\Text('/Assets/reCAPTCHA/reCAPTCHA.org.min.js');
            $text->set('site_key', $post->get('registration_key_site'));
            $text->save('/Assets/reCAPTCHA/reCAPTCHA.min.js');
        }

        if (!$post->get('registration_verify'))
        {
            $db->delete( table: TABLE_VERIFY_ACCOUNT );
        }

        // Edit registration settings
        $db->table(TABLE_SETTINGS, [
            'site_allow_forgot_password' => $post->get('registration_enabled') ? 0 : (int)$post->get('site_allow_forgot_password'),

            'registration_verify' => $post->get('registration_verify'),
            'registration_terms' => $post->get('registration_terms'),
            'registration_enabled' => (int)$post->get('registration_enabled'),
            'registration_key_site' => $post->get('registration_key_site'),
            'registration_key_secret' => $post->get('registration_key_secret')
        ]);

        // Update sessions
        $db->table(TABLE_SETTINGS, [
            'session' => RAND,
            'session_scripts' => RAND
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
    }
}