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
 * Index
 */
class Index extends \App\Page\Page
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
            'run/notification/up' => 'moveNotificationUp',
            'run/notification/down' => 'moveNotificationDown',
            'run/notification/delete' => 'deleteNotification',

            default => ''
        };
    }

    /**
     * Load data according to received ajax
     *
     * @param  string $ajax Received ajax
     * 
     * @return array Data
     */
    public function ajaxData( string $ajax )
    {
        return match($ajax)
        {
            'run/notification/up',
            'run/notification/down',
            'run/notification/delete' => [
                'id' => STRING
            ],

            default => []
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
        
        // If static mode is enabled or profiles are disabled
		if ($system->get('site_mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('settings')->elm2('notification')->active();
        
        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Notification.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();
        
        // List of notifications
        $notifications = $db->select('app.notification.all()');

        // Save list of notification's ids
        $data->set('data.notifications', array_column($notifications, 'notification_id'));

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Notification.json');
        $list->elm1('notification')->fill(data: $notifications, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count )
        { 
            $list
                ->set('data.html.ajax-id', $list->get('data.notification_id'))
                ->set('data.button.edit.href', '/admin/notification/show/' . $list->get('data.notification_id'));

            // Enable button to move notification up on all notifications except last
            if ($i !== 1)
            {
                $list->enable('data.button.up');
            }

            // Enable button to move notification down on all notifications except last
            if ($i !== $count)
            {
                $list->enable('data.button.down');
            }
        });
        $data->list = $list->getDataToGenerate();
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
    public function deleteNotification( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if notification exists
        if (!in_array($post->get('id'), $data->get('data.notifications')))
        {
            return false;
        }

        // Move previous notifications one position down
        $db->query('
            UPDATE ' . TABLE_NOTIFICATIONS . '
            LEFT JOIN ' . TABLE_NOTIFICATIONS . '2 ON n2.position_index > n.position_index
            SET n2.position_index = n2.position_index - 1
            WHERE n.notification_id = ?
        ', [$post->get('id')]);

        // Delete notification
        $db->delete(
            table: TABLE_NOTIFICATIONS,
            id: $post->get('id')
        );
    
        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Refresh page
        $data->set('options.refresh', true);
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
    public function moveNotificationUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if notification exists
        if (!in_array($post->get('id'), $data->get('data.notifications')))
        {
            return false;
        }

        // Move notification up
        $db->moveOnePositionUp( table: TABLE_NOTIFICATIONS, id: $post->get('id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
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
    public function moveNotificationDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if notification exists
        if (!in_array($post->get('id'), $data->get('data.notifications')))
        {
            return false;
        }

        // Move notification down
        $db->moveOnePositionDown( table: TABLE_NOTIFICATIONS, id: $post->get('id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }
}