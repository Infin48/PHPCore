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
 * Status
 */
class Status extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.?';
    
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
        $this->navbar->elm1('other')->elm2('status')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Status.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Status.json');

        $form
            ->form('status')

                // Setup extensions
                ->frame('extension')->get('body', function ( \App\Visualization\Form\Form $form, string $key )
                {
                    $form->input($key);

                    // If extension is loaded
                    if (extension_loaded($key))
                    {
                        // Mark extension by green color
                        $form->set('data.color', 'green');
                    }
                })

                // Setup writable files
                ->frame('writable')->get('body', function ( \App\Visualization\Form\Form $form, string $key )
                {
                    $form->input($key);

                    // If file is writable
                    if (is_writable(ROOT . $key))
                    {
                        // Mark path by green color
                        $form->set('data.color', 'green');
                    } 
                })

                // Setup executable files
                ->frame('exec')->get('body', function ( \App\Visualization\Form\Form $form, string $key )
                {
                    $form->input($key);

                    // If file is writable and executable
                    if (is_writable(ROOT . $key) and is_executable(ROOT . $key))
                    {
                        // Mark path by green color
                        $form->set('data.color', 'green');
                    } 
                });

        // Message about wrong php version
        $failedPHP = strtr($language->get('L_STATUS.L_PHP.L_ERROR'), ['{php_version}' => PHP_VERSION]);

        // Message about failed locale set
        $failedLoacaleWeb = strtr($language->get('L_STATUS.L_LOCALISATION.L_ERROR'), ['{locale}' => $system->get('site.locale')]);

        // Set to locale by default failed message
        $form->frame('other')->input('localeWeb')->set('data.titleIcon', $failedLoacaleWeb);

        // Set to locale by default failed message
        $form->frame('other')->input('php')->set('data.titleIcon', $failedPHP);

        // If localization was successfully set
        if (setlocale(LC_ALL, $system->get('site.locale') . '.UTF-8') !== false)
        {
            $form->frame('other')->input('localeWeb')
                // Mark locale by green color
                ->set('data.color', 'green')
                // Set current set locale name
                ->set('data.titleIcon', $system->get('site.locale'));
        }

        // If required PHP version is correct
        if (version_compare(PHP_VERSION, '8.0.0') >= 0)
        {
            // Mark version by green color and write current installed version
            $form->frame('other')->input('php')->set('data.color', 'green')->set('data.titleIcon', PHP_VERSION);
        }

        // Save form and get ready to generate
        $data->form = $form->getDataToGenerate();
    }
}