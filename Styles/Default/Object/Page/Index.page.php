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

namespace Style\Default\Page;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // Form
        $form = new \App\Visualization\Form\Form($data->form);
        $form->append('Style/Default/Form:/Formats/Setup.json');
        $form
            ->form('setup')
                ->callOnSuccess($this, 'editTemplateSettings')
                ->data($data->get('data.template.settings'))
                ->frame('setup')
                    ->input('delete_background_image', function ( \App\Visualization\Form\Form $form ) use ($data)
                    {
                        // If image exists
                        if ($data->get('data.template.settings.background_image'))
                        {
                            // Show delete background image checkbox
                            $form->show();
                        }
                    });
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
    public function editTemplateSettings( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // File
        $file = new \App\Model\File\File();

        // JSON
        $JSON = new \App\Model\File\JSON('/Styles/Default/Info.json');

        // If settings is not set
        if (!$JSON->get('settings'))
        {
            // Set as array
            $JSON->set('settings', []);
        }

        // Load image
        $image = $post->get('background_image');

        // If user uploaded background image
        if ($image->exists())
        {
            // Delete all older images
            $file->delete('/Styles/Default/Themes/Images/Header.*');

            // Upload new image
            $image->upload('/Styles/Default/Themes/Images', 'Header');

            // Save image path to JSON 
            $JSON->set('settings.background_image', '/Styles/Default/Themes/Images/Header.' . $image->getFormat() . '?' . mt_rand());
        }

        // Save background image position to JSON
        $JSON->set('settings.background_image_position', $post->get('background_image_position'));

        // If is checked "delete background image"
        if ($post->get('delete_background_image'))
        {
            // Delete image
            $file->delete('/Styles/Default/Themes/Images/Header.*');

            // Remove path in JSON
            $JSON->set('settings.background_image', '');
        }

        // Save JSON
        $JSON->save();

        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
    }
}