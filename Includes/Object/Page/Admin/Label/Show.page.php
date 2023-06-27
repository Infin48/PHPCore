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
    protected string $permission = 'admin.label';

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
        
        // If static mode is enabled
		if ($system->get('site_mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('forum')->elm2('label')->active();

        // Get data from database
        $row = $db->select('app.label.get()', $this->url->getID()) or $this->error404();

        // Save label data and unite with others
        $data->set('data.label', $row);

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Label.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.label.label_name'))->href('/admin/label/show/' . $data->get('data.label.label_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Label.json');
        $form
            ->form('label')
                ->callOnSuccess($this, 'editLabel')
                ->data($data->get('data.label'))
                // Setup form
                ->frame('label')
                    // Set title
                    ->title('L_LABEL.L_EDIT')
                    // Setup show button
                    ->input('show')->show()
                        // Set link to button
                        ->set('data.href', $this->url->build('/label/' . $this->url->getID() . '.' . $data->get('data.label.label_class')));

        // Finish form and get ready for generate
        $data->form = $form->getDataToGenerate();
        
        // Page title
        $data->set('data.head.title', $language->get('L_LABEL.L_LABEL') . ' - ' . $data->get('data.label.label_name'));
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
    public function editLabel( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $db->update(TABLE_LABELS, [
            'label_name'        => $post->get('label_name'),
            'label_color'       => $post->get('label_color'),
            'label_class'  => parse($post->get('label_name')) . $post->get('label_id'),
        ], $data->get('data.label.label_id'));

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
        
        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $post->get('label_name') );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Redirect user
        $data->set('data.redirect', '/admin/label/');
    }
}