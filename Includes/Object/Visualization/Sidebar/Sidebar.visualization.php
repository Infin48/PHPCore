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

namespace Visualization\Sidebar;

/**
 * Sidebar
 */
class Sidebar extends \Visualization\Visualization
{
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
     * @return void
     */
    public function left()
    {
        $this->side = 'left';
    }

    /**
     * Changes sidebar type to small
     *
     * @return void
     */
    public function small()
    {
        $this->type = 'small';
    }
    
    /**
     * This function will be executed before returning sidebar data
     *
     * @return void
     */
    protected function clb_getData()
    {   
        $this->obj->set->set('side', $this->side);
        $this->obj->set->set('type', $this->type);
    }
}
