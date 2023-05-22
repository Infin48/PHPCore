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

namespace App\Visualization\Panel;

/**
 * Panel
 */
class Panel extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to default language
     */
    protected array $translate = [
        'body.?.data.title',
        'body.?.data.form.title',
        'body.?.data.form.button.title',
        'body.?.body.?.data.title'
    ];

    /**
     * @var array $defaultValues List of keys and their default values
     */
    protected array $defaultValues = [];

    /**
     * @var array $parseToPath List of keys which their values will be parsed to path
     */
    protected array $parseToPath = [
        'body.?.body.?.options.template.body'
    ];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [
        'body.?.data.href',
        'body.?.body.?.data.href',
        'body.?.body.?.body.?.data.href'
    ];

    protected function clb_each_elm1()
    {
        // Remove empty dropdowns of forms
        if (in_array($this->get('options.type'), ['form', 'dropdown']))
        {
            if (!$this->get('body'))
            {
                $this->delete();

                return false;
            }
        }
    }
}
