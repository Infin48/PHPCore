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

namespace App\Page\Admin\Label;

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
    protected string $permission = 'admin.label';

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
            'run/label/up' => 'moveLabelUp',
            'run/label/down' => 'moveLabelDown',
            'run/label/delete' => 'deleteLabel',

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
            'run/label/up',
            'run/label/down',
            'run/label/delete' => [
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

        // If static mode is enabled
		if ($system->get('site_mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('label')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Label.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // List of labels
        $labels = $db->select('app.label.all()');

        // Save list of label's ids
        $data->set('data.labels', array_column($labels, 'label_id'));

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Label.json');
        $list->elm1('label')->fill(data: $labels, function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list, int $i, int $count )
        {
            $list 
                ->set('data.html.ajax-id', $list->get('data.label_id'))
                // Set links to buttons
                ->set('data.button.edit.href', '/admin/label/show/' . $list->get('data.label_id'))
                ->set('data.button.show.href', '/label/' . $list->get('data.label_id') . '.' . $list->get('data.label_class'));

            // Enable button to move label up on all labels except last
            if ($i !== 1)
            {
                $list->enable('data.button.up');
            }

            // Enable button to move label down on all labels except last
            if ($i !== $count)
            {
                $list->enable('data.button.down');
            }
        });
        $data->list = $list->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Label.json');
        $form->form('label')->callOnSuccess($this, 'newLabel');
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
    public function newLabel( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Move all labels one position up
        $db->moveOnePositionUp( table: TABLE_LABELS );

        $db->insert(TABLE_LABELS, [
            'label_name'        => $post->get('label_name'),
            'label_color'       => $post->get('label_color')
        ]);

        $db->update(TABLE_LABELS, [
            'label_class' => parse($post->get('label_name')) . $db->lastInsertId()
        ], $db->lastInsertId());

        // Synchronize labels
        $labels = $db->select('app.label.all()');

        $css = '';
        foreach ($labels as $label)
        {
            $css .= '.label.label--' . $label['label_class'] . '{background-color:' . $label['label_color'] . '}.label-checkbox.label--' . $label['label_class'] . '{color:' . $label['label_color'] . ' !important}.label--' . $label['label_class'] . ' input[type="checkbox"] + label .checkbox-icon{border-color:' . $label['label_color'] . '}.label--' . $label['label_class'] . ' input[type="checkbox"]:checked + label .checkbox-icon{background-color:' . $label['label_color'] . '}';
        }
        file_put_contents(ROOT . '/Includes/Template/css/Label.min.css', $css);

        // Update labels session
        $db->table(TABLE_SETTINGS, [
            'session_labels' => RAND
        ]);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('label_name') );
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
    public function deleteLabel( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if label exists
        if (!in_array($post->get('id'), $data->get('data.labels')))
        {
            return false;
        }

        $db->query('
            UPDATE ' . TABLE_LABELS . '
            LEFT JOIN ' . TABLE_LABELS . '2 ON l2.position_index > l.position_index
            SET l2.position_index = l2.position_index - 1
            WHERE l.label_id = ?
        ', [$post->get('id')]);

        // Remove label from topics and articles
        $db->query('
            DELETE l, tlb, alb
            FROM ' . TABLE_LABELS . '
            LEFT JOIN ' . TABLE_TOPICS_LABELS . ' ON tlb.label_id = l.label_id
            LEFT JOIN ' . TABLE_ARTICLES_LABELS . ' ON alb.label_id = l.label_id
            WHERE l.label_id = ?',
            [$post->get('id')]
        );

        // Update labels session
        $db->table(TABLE_SETTINGS, [
            'session_labels' => RAND
        ]);

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
    public function moveLabelUp( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if label exists
        if (!in_array($post->get('id'), $data->get('data.labels')))
        {
            return false;
        }

        // Move label up
        $db->moveOnePositionUp( table: TABLE_LABELS, id: $post->get('id') );
    
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
    public function moveLabelDown( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Check if label exists
        if (!in_array($post->get('id'), $data->get('data.labels')))
        {
            return false;
        }
        
        // Move label down
        $db->moveOnePositionDown( table: TABLE_LABELS, id: $post->get('id') );

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );
    }
}