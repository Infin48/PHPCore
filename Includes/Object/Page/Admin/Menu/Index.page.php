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

namespace App\Page\Admin\Menu;

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
    protected string $permission = 'admin.menu';

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
            'run/button/up' => 'moveButtonUp',
            'run/button/down' => 'moveButtonDown',
            'run/button/delete' => 'deleteButton',

            'run/sub-button/up' => 'moveSubButtonUp',
            'run/sub-button/down' => 'moveSubButtonDown',
            'run/sub-button/delete' => 'deleteSubButton',

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
            'run/button/up',
            'run/button/down',
            'run/button/delete',
            
            'run/sub-button/up',
            'run/sub-button/down',
            'run/sub-button/delete' => [
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
        // Navbar
        $this->navbar->elm1('settings')->elm2('menu')->active();
        
        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Menu.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Menu.json');

        // List of buttons
        $buttons = $db->select('app.button.all()');

        // Save list of button's ids
        $data->set('data.buttons', array_column($buttons, 'button_id'));

        // List with sub-button's ids
        $data->set('data.sub-buttons', []);

        // Buttons
        $list->elm1('button')->fill(data: $buttons, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count ) use ($db, $data)
        {
            $list
                ->set('data.title', $list->get('data.button_name'))
                ->set('data.html.ajax-id', $list->get('data.button_id'))
                ->set('data.button.edit.href', '/admin/menu/show/' . $list->get('data.button_id') . '/type-' . ($list->get('data.button_dropdown') ? 'dropdown' : 'button'));

            // Enable button to move button down on all buttons except last
            if ($i !== 1)
            {
                $list->enable('data.button.up');
            }

            // Enable button to move button down on all buttons except last
            if ($i !== $count)
            {
                $list->enable('data.button.down');
            }

            // If button is dropdown
            // then fill button with sub-buttons
            if ($list->get('data.button_dropdown') == 1)
            {
                $list
                    ->set('data.button.add-sub-button.href', '/admin/menu/add/' . $list->get('data.button_id') . '/type-subbutton/')
                    ->show('data.button.add-sub-button');


                $subButtons = $db->select('app.sub-button.parent()', $list->get('data.button_id'));

                $data->set('data.sub-buttons', array_merge($data->get('data.sub-buttons'), array_column($subButtons, 'button_sub_id')));

                $list->fill(data: $subButtons, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count )
                {
                    $list
                        ->set('data.title', $list->get('data.button_sub_name'))
                        ->set('data.html.ajax-id', $list->get('data.button_sub_id'))
                        ->set('data.button.edit.href', '/admin/menu/show/' . $list->get('data.button_sub_id') . '/type-subbutton/');

                    // Enable button to move sub-button down on all sub-buttons except last
                    if ($i !== 1)
                    {
                        $list->enable('data.button.up');
                    }
        
                    // Enable button to move sub-button down on all sub-buttons except last
                    if ($i !== $count)
                    {
                        $list->enable('data.button.down');
                    }
                });
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
    public function deleteButton( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if button exists
        if (!in_array($post->get('id'), $data->get('data.buttons')))
        {
            return false;
        }

        // Move all previous buttons one position down
        $db->query('
            UPDATE ' . TABLE_BUTTONS . '
            LEFT JOIN ' . TABLE_BUTTONS . '2 ON b2.position_index > b.position_index
            SET b2.position_index = b2.position_index - 1
            WHERE b.button_id = ?
        ', [$post->get('id')]);

        // Delete button
        $db->query('DELETE b, bs FROM ' . TABLE_BUTTONS . ' LEFT JOIN ' . TABLE_BUTTONS_SUB . ' ON bs.button_id = b.button_id WHERE b.button_id = ?', [$post->get('id')]);
    
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
    public function deleteSubButton( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if sub-button exists
        if (!in_array($post->get('id'), $data->get('data.sub-buttons')))
        {
            return false;
        }

        // Move all previous sub buttons one position down
        $db->query('
            UPDATE ' . TABLE_BUTTONS_SUB . '
            LEFT JOIN ' . TABLE_BUTTONS_SUB . '2 ON bs2.position_index > bs.position_index AND bs2.button_id = bs.button_id
            SET bs2.position_index = bs2.position_index - 1
            WHERE bs.button_sub_id = ?
        ', [$post->get('id')]);

        // Delete sub button
        $db->delete(
            table: TABLE_BUTTONS_SUB,
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
    public function moveButtonUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if button exists
        if (!in_array($post->get('id'), $data->get('data.buttons')))
        {
            return false;
        }

        // Move button up
        $db->moveOnePositionUp( table: TABLE_BUTTONS, id: $post->get('id') );

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
    public function moveButtonDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if button exists
        if (!in_array($post->get('id'), $data->get('data.buttons')))
        {
            return false;
        }

        // Move button down
        $db->moveOnePositionDown( table: TABLE_BUTTONS, id: $post->get('id') );

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
    public function moveSubButtonUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if sub-button exists
        if (!in_array($post->get('id'), $data->get('data.sub-buttons')))
        {
            return false;
        }

        // Move sub button up
        $db->query('
            UPDATE ' . TABLE_BUTTONS_SUB . '
            LEFT JOIN ' . TABLE_BUTTONS_SUB . '2 ON bs2.position_index = bs.position_index + 1 AND bs2.button_id = bs.button_id
            SET bs.position_index = bs.position_index + 1,
                bs2.position_index = bs2.position_index - 1
            WHERE bs.button_sub_id = ? AND bs2.button_sub_id IS NOT NULL
        ', [$post->get('id')]);

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
    public function moveSubButtonDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if sub-button exists
        if (!in_array($post->get('id'), $data->get('data.sub-buttons')))
        {
            return false;
        }
        
        // Move sub button up
        $db->query('
            UPDATE ' . TABLE_BUTTONS_SUB . '
            LEFT JOIN ' . TABLE_BUTTONS_SUB . '2 ON bs2.position_index = bs.position_index - 1 AND bs2.button_id = bs.button_id
            SET bs.position_index = bs.position_index - 1,
                bs2.position_index = bs2.position_index + 1
            WHERE bs.button_sub_id = ? AND bs2.button_sub_id IS NOT NULL
        ', [$post->get('id')]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }
}