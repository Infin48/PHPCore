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
    protected string $permission = 'admin.menu';

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

        switch ($this->url->get('type'))
        {
            case 'dropdown':
                $form = 'Root/Form:/Formats/Admin/Menu/Dropdown.json';
                $title = 'L_DROPDOWN.L_NEW';
                $callOnSuccess = 'newDropdown';
            break;

            case 'subbutton':
                $form = 'Root/Form:/Formats/Admin/Menu/Sub.json';
                $title = 'L_BUTTON.L_NEW';
                $callOnSuccess = 'newSubButton';

                $button = $db->select('app.button.get()', $this->url->getID()) or $this->error404();

                // Save button data
                $data->set('data.button', $button);

                // Check if button is dropdown
                if ($data->get('data.button.button_drodpown') == 0)
                {
                    $this->error404();
                }

            break;

            default:
                $form = 'Root/Form:/Formats/Admin/Menu/Button.json';
                $title = 'L_BUTTON.L_NEW';
                $callOnSuccess = 'newButton';
            break;
        }

        // Form
        $form = new \App\Visualization\Form\Form($form);
        $form
            ->form('button')
                ->callOnSuccess($this, $callOnSuccess)
                ->frame('button')
                    ->title($title);
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
    public function newButton( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all buttons one position up
        $db->moveOnePositionUp( table: TABLE_BUTTONS );

        // Add button
        $db->insert(TABLE_BUTTONS, [
            'button_name'       => $post->get('button_name'),
            'button_link'       => $post->get('button_link'),
            'button_icon'       => $post->get('button_icon') ? 'fa-' .$post->get('button_icon_style') . ' fa-' . $post->get('button_icon') : '',
            'position_index'    => '1'
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('button_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect user
        $data->set('data.redirect', '/admin/menu/');
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
    public function newSubButton( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all sub buttons from dropdown one position up
        $db->query('
            UPDATE ' . TABLE_BUTTONS_SUB . '
            SET bs.position_index = bs.position_index + 1
            WHERE bs.button_id = ?
        ', [$data->get('data.button.button_id')]);

        // Add sub button
        $db->insert(TABLE_BUTTONS_SUB, [
            'button_id'             => $data->get('data.button.button_id'),
            'position_index'        => '1',
            'button_sub_name'       => $post->get('button_sub_name'),
            'button_sub_link'       => $post->get('button_sub_link')
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('button_sub_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect user
        $data->set('data.redirect', '/admin/menu/');
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
    public function newDropdown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all buttons one position up
        $db->moveOnePositionUp( table: TABLE_BUTTONS );

        // Adds button
        $db->insert(TABLE_BUTTONS, [
            'button_name'       => $post->get('button_name'),
            'button_icon'       => $post->get('button_icon') ? 'fa-' . $post->get('button_icon_style') . ' fa-' . $post->get('button_icon') : '',
            'button_dropdown'   => '1'
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('button_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/menu/');
    }
}