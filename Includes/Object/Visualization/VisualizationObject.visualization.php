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
     * Sets object
     * 
     * @param array $object
     */
    public function __construct( array $object )
    {
        $this->is = new VisualizationObjectIs($object);
        $this->get = new VisualizationObjectGet($object);
        $this->set = new VisualizationObjectSet($object);
    }

    /**
     * Returns edited object
     *
     * @return array
     */
    public function getObject()
    {
        return $this->set->delete->object;
    }
}