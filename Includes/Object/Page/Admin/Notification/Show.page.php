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
 * Show
 */
class Show extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;
    
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
        
        // Language
        $language = $data->get('inst.language');
        
        // If static mode is enabled or profiles are disabled
		if ($system->get('site_mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('settings')->elm2('notification')->active();

        // Get notification data from database
        $row = $db->select('app.notification.get()', $this->url->getID()) or $this->error404();

        // Save notification data
        $data->set('data.notification', $row);

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Notification.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.notification.notification_name'))->href('/admin/notification/show/' . $data->get('data.notification.notification_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Notification.json');
        $form
            ->form('notification')
                ->data($data->get('data.notification'))
                ->callOnSuccess($this, 'editNotification')    
                ->frame('notification')
                    ->title('L_NOTIFICATION.L_EDIT');
        $data->form = $form->getDataToGenerate();

        // Page title
        $data->set('data.head.title', $language->get('L_NOTIFICATION.L_NOTIFICATION') . ' - ' . $data->get('data.notification.notification_name'));
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
    public function editNotification( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Edit notification
        $db->update(TABLE_NOTIFICATIONS, [
            'notification_name'     => $post->get('notification_name'),
            'notification_text'     => $post->get('notification_text'),
            'notification_type'     => $post->get('notification_type'),
            'notification_hidden'   => $post->get('notification_hidden')
        ], $data->get('data.notification.notification_id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('notification_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/notification/');
    }
}