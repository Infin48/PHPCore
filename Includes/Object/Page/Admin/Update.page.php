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

namespace App\Page\Admin;

/**
 * Update
 */
class Update extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Update.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.settings';
    
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

        // Navbar
        $this->navbar->elm1('other')->elm2('update')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Update.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Notification
        $notification = new \App\Visualization\Notification\Notification($data->notification);
        $notification
            // Create new object(notification) and jump inside
            ->create()->jumpTo()
            // Set name
            ->set('data.name', 'update')
            // Set type
            ->set('data.type', 'warning')
            // Set title
            ->set('data.title', $language->get('L_NOTIFI.L_UPDATE.L_TITLE'));
        $data->notification = $notification->getDataToGenerate();

        // Block
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Update.json');
        $block
            // Set current PHPCore version
            ->elm1('version')->value(PHPCORE_VERSION)
            // Set date of last update
            ->elm1('last_updated')->value($this->build->date->short($system->get('site.updated')));

        // Save block and get ready to generate
        $data->block = $block->getDataToGenerate();

        // Load JSON from GitHub
        $JSON = new \App\Model\File\JSON('https://api.github.com/repos/Infin48/PHPCore/releases');
        
        // If exists release
        if ($JSON->get('0'))
        {
            // If this latest release is newer then installed 
            if (version_compare($JSON->get('0.tag_name'), PHPCORE_VERSION, '>'))
            {
                // Form
                $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Update.json');
                $form
                    ->form('update')
                        // Fill form with data
                        ->data($JSON->get('0'))
                        // Remove buttons
                        ->disButtons()
                        ->frame('available')
                            // Set link to details button
                            ->input('details')->set('data.href', '$' . $JSON->get('0.html_url'))
                            // Set link to download button
                            ->input('download')->set('data.href', '$' . $JSON->get('0.zipball_url'));

                // Save form and get ready to generate
                $data->form = $form->getDataToGenerate();
            }
        }
    }
}