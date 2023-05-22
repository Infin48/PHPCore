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

namespace App\Page\Admin\Notification;

/**
 * Add
 */
class Add extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.notification';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');
        
        // If static mode is enabled or profiles are disabled
		if ($system->get('site.mode') == 'static')
		{
            // Show error page
			$this->error404();
		}

        // Navbar
        $this->navbar->elm1('settings')->elm2('notification')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Notification.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Notification.json');
        $form
            ->form('notification')
                ->callOnSuccess($this, 'newNotification')    
                ->frame('notification')
                    ->title('L_NOTIFICATION.L_NEW')
                    ->input('notification_type')
                        ->elm4('info')
                            ->check();
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
    public function newNotification( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all notifications one position up
        $db->moveOnePositionUp( table: TABLE_NOTIFICATIONS );

        // Adds notificaion
        $db->insert(TABLE_NOTIFICATIONS, [
            'notification_hidden'   => $post->get('notification_hidden'),
            'notification_name'     => $post->get('notification_name'),
            'notification_text'     => $post->get('notification_text'),
            'notification_type'     => $post->get('notification_type')
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('notification_name'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/notification/');
    }
}