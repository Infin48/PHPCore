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
 * Signature
 */
class Signature extends \App\Page\Page
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
    protected string $template = 'Root/Style:/Templates/User/Signature.phtml';
    
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
        if ($system->get('site_mode') == 'blog')
        {
            // Show error page
            $this->error404();
        }
        
        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/User/Index.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/User/Signature.json');
        $form
            ->form('signature')
                ->data($user->get())
                ->callOnSuccess($this, 'editSignature');
        $data->form = $form->getDataToGenerate();

        // Sidebar
        $sidebar = new \App\Visualization\Sidebar\Sidebar('Root/Sidebar:/Formats/User.json');
        $sidebar
            // Set sidebar to show on left side
            ->left()
            // Set small version
            ->small()
            ->elm1('basic')
                // Show signature button and select
                ->elm2('signature')->show()->select()
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
    public function editSignature( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Set signature
        $db->update(TABLE_USERS, [
            'user_signature' => $post->get('user_signature')
        ], LOGGED_USER_ID);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
    }
}