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

namespace Visualization;

/**
 * VisualizationObject
 */
class VisualizationObject
{
    /**
     * @var array $object Object
     */
    public array $object;

    /**
     * @var \Visualization\VisualizationObjectIs $is VisualiaztionObjectIs
     */
    public VisualizationObjectIs $is;

    /**
     * @var \Visualization\VisualizationObjectGet $get VisualiaztionObjectGet
     */
    public VisualizationObjectGet $get;

    /**
     * @var \Visualization\VisualizationObjectSet $set VisualiaztionObjectSet
     */
    public VisualizationObjectSet $set;

    /**
     * @var \Visualization\VisualizationObjectDelete $delete VisualizationObjectDelete
     */
    public VisualizationObjectDelete $delete;

    /**
     * Sets object
     * 
     * @param array $object
     */
    public function __construct( array $object )
    {
        $this->object = $object;

        $this->is = new VisualizationObjectIs($this);
        $this->get = new VisualizationObjectGet($this);
        $this->set = new VisualizationObjectSet($this);
        $this->delete = new VisualizationObjectDelete($this);
    }

    /**
     * Returns edited object
     *
     * @return array
     */
    public function getObject()
    {
        return $this->object;
    }
}