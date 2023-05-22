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
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/User/About.phtml';
    
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
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Index.json');
        $form
            ->form('about')
                ->data($user->get());

        // If blog mode is enabled and profiles are disabled
        if ($system->get('site.mode') == 'blog' and $system->get('site.mode.blog.profiles') == 0)
        {
            // Delete button from form
            $form->disButtons();
        } else $form->callOnSuccess($this, 'editAbout');

        $form
            ->frame('about')
                
                // Setup user_about textarea
                ->input('user_about', function ( \App\Visualization\Form\Form $form ) use ($system)
                {
                    // If blog mode is enabled
                    if ($system->get('site.mode') == 'blog')
                    {
                        // If profiles are disabled
                        if ($system->get('site.mode.blog.profiles') == 0)
                        {
                            // Hide this input
                            $form->hide();

                            // Delete button from form
                            $form->disButtons();

                        } else $form->callOnSuccess($this, 'editAbout');
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
                ->elm2('about')->select()
                // Set position to settings button
                ->elm2('settings', function ($sidebar) use ($system)
                {
                    // If is enabled blog mode
                    if ($system->get('site.mode') == 'blog')
                    {
                        // And also profiles are disabled
                        if ($system->get('site.mode.blog.profiles') == 0)
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
    public function editAbout( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $db->update(TABLE_USERS, [
            'user_about' => $post->get('user_about')
        ], LOGGED_USER_ID);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
    }
}