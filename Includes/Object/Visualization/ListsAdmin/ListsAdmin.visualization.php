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

namespace App\Visualization\ListsAdmin;

use App\Model\Url;

/**
 * ListsAdmin
 */
class ListsAdmin extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to language
     */
    protected array $translate = [
        'body.?.data.title',
        'body.?.data.empty',
        'body.?.data.button.?.title',
        'body.?.body.?.data.label.?.text',
        'body.?.body.?.data.button.?.title',
    ];

    /**
     * @var array $defaultValues List of keys and their default values
     */
    protected array $defaultValues = [];

    /**
     * @var array $parseToPath List of keys which their values will be parsed to path
     */
    protected array $parseToPath = [
        'body.?.body.?.data.button.?.template',
        'body.?.body.?.body.?.data.button.?.template',
        'body.?.body.?.body.?.options.template.big',
        'body.?.body.?.options.template.big',
        'body.?.body.?.options.template.small',
        'body.?.body.?.options.template.medium'
    ];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [
        'body.?.data.button.?.href',
        'body.?.body.?.data.button.?.href',
        'body.?.body.?.body.?.data.button.?.href'
    ];

    /**
     * Adds label to current object
     *
     * @param  string $color Label color
     * @param  string $icon Label icon
     * @param  string $text Label text
     * 
     * @return void
     */
    public function addLabel( string $color, string $icon = null, string $text = null )
    {
        $this->set('data.label', array_merge($this->get('data.label') ?: [], [['color' => $color, 'text' => $text, 'icon' => $icon ?? '']]));
    }

    /**
     * Executes code for every object
     *
     * @param  \Visualization\Visualization $this->obj
     * 
     * @return void|false
     */
    protected function clb_each()
    {
        foreach ($this->obj->get('data.button') ?: [] as $btnName => $btn)
        {
            if ($this->obj->get('data.button.' . $btnName . '.hide') == true)
            {
                $this->obj->delete('data.button.' . $btnName);
                continue;
            }

            if (!isset($btn['title']))
            {
                if (!isset($btn['template']))
                {
                    // Assign button template
                    $btn['template'] = 'Root/Style:/Templates/Blocks/Visualization/Lists/Buttons/' . ucfirst($btnName) . '.phtml';
                }
            }

            // If button has href parameter
            if (isset($btn['href']))
            {
                // Assign variables to url
                foreach ($this->obj->get('data') as $key => $value)
                {
                    if (!is_array($value))
                    {
                        $btn['href'] = strtr($btn['href'], ['{' . $key . '}' => $value]);
                    }
                }
            }

            // Set edited data to button
            $this->obj->set('data.button.' . $btnName, $btn);
        }
    }
}