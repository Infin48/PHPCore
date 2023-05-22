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
 * Settings
 */
class Settings extends \App\Page\Page
{
    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/User/Settings.phtml';
    
    /**
     * Body of page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // If blog mode is enabled
        if ($system->get('site.mode') == 'blog')
        {
            // If profiles are disabled
            if ($system->get('site.mode.blog.profiles') == 0)
            {
                // Show error page
                $this->error404();
            }
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/User/Index.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Settings.json');
        $form
            ->form('settings')
                ->data($user->get())
                ->callOnSuccess($this, 'editSettings')
                ->frame('settings')
                    ->input('delete_user_profile_image', function ( \App\Visualization\Form\Form $form ) use ($user)
                    {
                        if ($user->get('user_profile_image') and !in_array($user->get('user_profile_image'), PROFILE_IMAGES_COLORS))
                        {
                            $form->show();
                        }
                    })
                    ->input('delete_user_header_image', function ( \App\Visualization\Form\Form $form ) use ($user)
                    {
                        if ($user->get('user_header_image'))
                        {
                            $form->show();
                        }
                    });
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
                ->elm2('settings')->select()
            ->elm1('basic')
                // Set position to signature button
                ->elm2('signature', function ($sidebar) use ($system)
                {
                    // If is enabled blog mode
                    if ($system->get('site.mode') != 'blog')
                    {
                        // Show this button in sidebar
                        $sidebar->show();
                    }
                })
            // Set position to conversation sidebar
            ->elm1('conversation', function ($sidebar) use ($system)
            {
                // If is enabled blog mode
                if ($system->get('site.mode') != 'blog')
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
    public function editSettings( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if (!ctype_digit($post->get('user_age')) or $post->get('user_age') <= 0)
        {
            $post->set('user_age', '');
        }
        
        // File model
        $file = new \App\Model\File\File();

        // Load profile image
        $image = $post->get('user_profile_image');
        if ($image->exists())
        { 
            $file->mkdir('/Uploads/Users/' . LOGGED_USER_ID);

            // Resize
            $image->resize(200, 200);

            // Compress
            $image->compress(50);

            // Delete old profile image
            $file->delete('/Uploads/Users/' . LOGGED_USER_ID . '/Profile.*');

            // Upload image
            $image->upload('/Uploads/Users/' . LOGGED_USER_ID, 'Profile');

            // Set image
            $db->update(TABLE_USERS, [
                'user_profile_image' => $image->getFormat() . '?' . RAND
            ], LOGGED_USER_ID);
        }

        // Load header image
        $image = $post->get('user_header_image');
        if ($image->exists())
        {
            $file->mkdir('/Uploads/Users/' . LOGGED_USER_ID);

            // Delete old header image
            $file->delete('/Uploads/Users/' . LOGGED_USER_ID . '/Header.*');

            // Compress
            $image->compress(50);

            // Upload image
            $image->upload('/Uploads/Users/' . LOGGED_USER_ID, 'Header');

            // Set image
            $db->update(TABLE_USERS, [
                'user_header_image' => $image->getFormat() . '?' . RAND
            ], LOGGED_USER_ID);
        }
        
        // If delete profile image
        if ($post->get('delete_user_profile_image')) {

            // Delete image
            $file->delete('/Uploads/Users/' . LOGGED_USER_ID . '/Profile.*');

            // Set image
            $db->update(TABLE_USERS, [
                'user_profile_image' => getProfileImageColor()
            ], LOGGED_USER_ID);
        }

        // If delete header image
        if ($post->get('delete_user_header_image')) {

            // Delete image
            $file->delete('/Uploads/Users/' . LOGGED_USER_ID . '/Header.*');

            // Set image
            $db->update(TABLE_USERS, [
                'user_header_image' => ''
            ], LOGGED_USER_ID);
        }
        
        // Update user informations
        $db->update(TABLE_USERS, [
            'user_age' 		        => $post->get('user_age') ?: '',
            'user_text'             => $post->get('user_text'),
            'user_gender' 	        => $post->get('user_gender'),
            'user_discord'         => $post->get('user_discord'),
            'user_location'         => $post->get('user_location'),
            'user_facebook'         => $post->get('user_facebook'),
            'user_instagram'         => $post->get('user_instagram')
        ], LOGGED_USER_ID);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
    }
}