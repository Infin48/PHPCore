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

namespace App\Visualization\Navbar;

/**
 * Navbar
 */
class Navbar extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to default language
     */
    protected array $translate = [
        'body.logged.body.notification.data.empty',
        'body.logged.body.notification.data.menu.title',
        'body.?.data.title',
        'body.?.body.?.data.title',
        'body.?.body.?.body.?.data.title'
    ];

    /**
     * @var array $defaultValues List of keys and their default values
     */
    protected array $defaultValues = [
        'body.?.body.?.data.href' => '',
        'body.?.body.?.data.title' => '',
        'body.?.body.?.data.icon' => '',
        'body.?.body.?.body' => [],
        'body.?.body.?.data.notifiCount' => '',
        'body.?.body.?.options.type' => 'button',
        'body.?.body.?.options.disabled' => false
    ];

    /**
     * @var array $parseToPath List of keys which their values will be parsed to path
     */
    protected array $parseToPath = [
        'body.?.body.?.options.template.root',
        'body.?.body.?.options.template.body',
        'body.?.body.?.body.?.options.template.body'
    ];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [
        'body.?.body.?.data.href',
        'body.?.body.?.body.?.data.href'
    ];

    /**
     * @var string $languagePrefix Default language prefix
     */
    protected string $languagePrefix = 'L_NAVBAR';

    /**
     * @var \App\Model\Permission $perm Permission
     */
    public \App\Model\Permission $perm;

    /**
     * Adds icon notification
     *
     * @param string $unicode Icon unicode
     * 
     * @return $this
     */
    public function notifiIcon( string $unicode )
    {
        $this->set('data.notifiIcon', $unicode);
        return $this;
    }

    /**
     * Actives button in navbar
     *
     * @return $this
     */
    public function active()
    {
        $this->set('options.active', true);
        return $this;
    }
    
    /**
     * Executes code for every object
     *
     * @param  \Visualization\Visualization $visual
     * 
     * @return void|false
     */
    protected function each_clb( \Visualization\Visualization $visual )
    {
        if ($visual->obj->is('options.permission'))
        {
            if ($this->perm->has($visual->get('options.permission')) === false)
            {
                return false;
            }
        }

        if ($visual->obj->is('options.disabled') === true)
        {
            $visual->obj->set('data.href', '');
        }
    }
}
