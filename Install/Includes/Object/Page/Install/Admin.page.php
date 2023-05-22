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

namespace App\Page\Install;

/**
 * Admin
 */
class Admin extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = '/Install/Admin.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database $db )
    {
        $form = new \App\Visualization\Form\Form('/Admin.json');
        $form->callOnSuccess($this, 'setupAdmin');
        $data->form = $form;
    }

    public function setupAdmin( \App\Model\Data $data, \App\Model\Database $db, \App\Model\Post $post )
    {
        $check = new \App\Model\Check();

        $check->userName($post->get('user_name'));
        $check->email($post->get('user_email'));
        $check->password($post->get('user_password'));

        $db->insert(TABLE_USERS, [
            'user_id' => 1,
            'user_name' => $post->get('user_name'),
            'user_email' => $post->get('user_email'),
            'user_password' => password_hash($post->get('user_password'), PASSWORD_DEFAULT),
            'user_profile_image' => getProfileImageColor(),
            'group_id' => 1,
            'user_topics' => 1
        ]);

        $JSON = new \App\Model\JSON('/Install/Includes/Settings.json');
        $JSON->set('db', true);
        $JSON->save();

        redirect('/install/settings/');
    }
}