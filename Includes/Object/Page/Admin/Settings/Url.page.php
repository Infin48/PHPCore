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

namespace App\Page\Admin\Settings;

/**
 * Url
 */
class Url extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.url';

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
            'run/url/delete' => 'deleteTranslateForURL',

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
            'run/url/delete' => [
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
        $this->navbar->elm1('settings')->elm2('settings')->active()->elm3('url')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Settings/URL.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Settings/URL.json');
        $list->elm1('defaults')->fill(data: $db->select('app.settings.URLDefault()'), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list )
        {
            $list->set('data.html.ajax-id', $list->get('data.settings_url_id'));
        });
        
        $list->elm1('hidden')->fill(data: $db->select('app.settings.URLHidden()'), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list )
        {
            $list->set('data.html.ajax-id', $list->get('data.settings_url_id'));
        });
        $data->list = $list->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Settings/URL.json');
        $form
            ->form('url')
                ->callOnSuccess($this, 'newTranslateForURL');
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
    public function newTranslateForURL( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // If entered urls doesn't start or end with slash
        if (
            !str_starts_with($post->get('settings_url_from'), '/') or
            !str_starts_with($post->get('settings_url_to'), '/') or
            (
                !str_ends_with($post->get('settings_url_from'), '/') and
                !str_ends_with($post->get('settings_url_from'), '-')
            ) or
            (
                !str_ends_with($post->get('settings_url_to'), '/') and
                !str_ends_with($post->get('settings_url_to'), '-')
            )
        ) {
            throw new \App\Exception\Notice('settings_url_error');
        }

        // If entered url doesnt end with same cgaracter
        if (str_ends_with($post->get('settings_url_from'), '/'))
        {
            if (!str_ends_with($post->get('settings_url_to'), '/'))
            {
                throw new \App\Exception\Notice('settings_url_end_same');
            }
        }

        // If entered url doesnt end with same cgaracter
        if (str_ends_with($post->get('settings_url_from'), '-'))
        {
            if (!str_ends_with($post->get('settings_url_to'), '-'))
            {
                throw new \App\Exception\Notice('settings_url_end_same');
            }
        }

        // Add translate
        $db->insert(TABLE_SETTINGS_URL, [
            'settings_url_from'         => $post->get('settings_url_from'),
            'settings_url_to'           => $post->get('settings_url_to'),
            'settings_url_hidden'       => (int)$post->get('settings_url_hidden')
        ]);

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
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
    public function deleteTranslateForURL( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Delete url
        $db->delete(
            table: TABLE_SETTINGS_URL,
            id: $post->get('id')
        );

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
        
        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Refresh page
        $data->set('options.refresh', true);
    }
}