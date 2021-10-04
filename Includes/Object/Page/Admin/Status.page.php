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

namespace Page\Admin;

use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Status
 */
class Status extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Status',
        'permission' => 'admin.?'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('other')->row('status')->active();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FIELD
        $field = new Field('/Admin/Status');
        $field->disButtons();
        $field->object('extension');

        foreach (['GD', 'mbstring', 'PDO', 'PDO_mysql', 'SPL', 'zip'] as $ext) {

            if (extension_loaded($ext)) {
                $field->row($ext)->setData('color', 'green');
            }

        }
        $field->object('writable');
        foreach (['/Includes/.htdata.json', '/Includes/Template/css/Group.min.css', '/Includes/Template/css/Label.min.css'] as $file) {
            
            if (is_writable(ROOT . $file)) {
                $field->row($file)->setData('color', 'green');
            }  
        }

        $failedLoacaleWeb = strtr($this->language->get('L_STATUS_LOCALISATION_FAILED'), ['{locale}' => $this->system->get('site.locale')]);

        $field->object('other')->row('localeWeb')->setData('titleIcon', '$' . $failedLoacaleWeb);

        if (setlocale(LC_ALL, $this->system->get('site.locale') . '.UTF-8') !== false) {
            $field->object('other')->row('localeWeb')
                ->setData('color', 'green')
                ->setData('titleIcon', '$' . $this->system->get('site.locale'));
        }

        $this->data->field = $field->getData();
    }
}