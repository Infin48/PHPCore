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

namespace App\Visualization\Sidebar;

/**
 * Sidebar
 */
class Sidebar extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to language
     */
    protected array $translate = [
        'body.?.data.title',
        'body.?.data.empty',
        'body.?.body.?.data.empty',
        'body.?.body.?.data.title',
        'body.?.body.?.data.button',
        'body.?.body.?.body.?.data.title',
        'body.?.body.?.body.?.data.value'
    ];

    /**
     * @var array $defaultValues List of keys and their default values
     */
    protected array $defaultValues = [];

    /**
     * @var array $parseToPath List of keys which their values will be parsed to path
     */
    protected array $parseToPath = [
        'body.?.options.template.root',
        'body.?.options.template.body',
        'body.?.body.?.options.template.body',
        'body.?.body.?.options.template.root',
        'body.?.body.?.body.?.options.template.body'
    ];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [
        'body.?.body.?.data.href',
        'body.?.body.?.body.?.data.href',
    ];

    /**
     * @var string $side Side of page where sidebar will be displayed
     */
    private string $side = 'right';

    /**
     * @var string $type Sidebar type
     */
    private string $type = 'default';

    /**
     * Shows sidebar on left side
     *
     * @return $this
     */
    public function left()
    {
        $this->side = 'left';

        return $this;
    }

    /**
     * Changes sidebar type to small
     *
     * @return $this
     */
    public function small()
    {
        $this->type = 'small';

        return $this;
    }
    
    /**
     * This function will be executed before returning sidebar data
     *
     * @return void
     */
    protected function clb_getData()
    {   
        $this->set('options.side', $this->side);
        $this->set('options.type', $this->type);
    }
}
