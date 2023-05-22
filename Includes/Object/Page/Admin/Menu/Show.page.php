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
 * Show
 */
class Show extends \App\Page\Page
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
        // Language
        $language = $data->get('inst.language');
        
        // Navbar
        $this->navbar->elm1('settings')->elm2('menu')->active();
        
        switch ($this->url->get('type'))
        {
            case 'dropdown':
                $select = 'button';
                $form = 'Root/Form:/Formats/Admin/Menu/Dropdown.json';
                $title = 'L_DROPDOWN.L_EDIT';
                $callOnSuccess = 'editDropdown';
            break;

            case 'subbutton':
                $select = 'sub-button';
                $form = 'Root/Form:/Formats/Admin/Menu/Sub.json';
                $title = 'L_BUTTON.L_EDIT';
                $callOnSuccess = 'editSubButton';
            break;

            default:
                $select = 'button';
                $form = 'Root/Form:/Formats/Admin/Menu/Button.json';
                $title = 'L_BUTTON.L_EDIT';
                $callOnSuccess = 'editButton';
            break;
        }

        // Get button data brom database
        $row = $db->select('app.' . $select . '.get()', $this->url->getID()) or $this->error404();

        // Save button data
        $data->set('data.button', $row);

        if ($this->url->get('type') != 'subbutton')
        {
            if ($data->get('data.button.button_icon'))
            {
                $ex = explode(' ', str_replace('fa-', '', $data->get('data.button.button_icon')));
                $data->set('data.button.button_icon', $ex[1]);
                $data->set('data.button.button_icon_style', str_replace('fa-', '', $ex[0]));
            }
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Menu.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.button.button_name') ?: $data->get('data.button.button_sub_name'))->href('/admin/menu/button/show/' . $data->get('data.button.button_id') ?: $data->get('data.button.button_sub_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form($form);
        $form
            ->form('button')
                ->callOnSuccess($this, $callOnSuccess)
                ->data($data->get('data.button'))
                ->frame('button')
                    ->title($title);
        $data->form = $form->getDataToGenerate();

        // Page title
        $data->set('data.head.title', $language->get('L_BUTTON.L_BUTTON') . ' - ' . $data->get('data.button.button_name') ?: $data->get('data.button.button_sub_name'));
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
    public function editButton( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Update button
        $db->update(TABLE_BUTTONS, [
            'button_name'       => $post->get('button_name'),
            'button_link'       => $post->get('button_link'),
            'button_icon'       => $post->get('button_icon') ? 'fa-' . $post->get('button_icon_style') . ' fa-' . $post->get('button_icon') : ''
        ], $data->get('data.button.button_id'));

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
    public function editSubButton( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Edit sub button
        $db->update(TABLE_BUTTONS_SUB, [
            'button_sub_name'       => $post->get('button_sub_name'),
            'button_sub_link'       => $post->get('button_sub_link')
        ], $data->get('data.button.button_sub_id'));

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
    public function editDropdown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Edit dropdown
        $db->update(TABLE_BUTTONS, [
            'button_name'       => $post->get('button_name'),
            'button_icon'       => $post->get('button_icon') ? 'fa-' . $post->get('button_icon_style') . ' fa-' . $post->get('button_icon') : '',
        ], $data->get('data.button.button_id'));
    
        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('button_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/menu/');
    }
}