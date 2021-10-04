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

namespace Model;

/**
 * Data
 */
class Data 
{
    /**
     * @var array $data Page data
     */
    public array $data = [];

    /**
     * @var array $block Block data
     */
    public array $block = [];

    /**
     * @var array $filed Field data
     */
    public array $field = [];

    /**
     * @var array $list List data
     */
    public array $list = [];

    /**
     * @var array $head Head data
     */
    public array $head = [];

    /**
     * @var string $chart Chart data
     */
    public string $chart = '';

    /**
     * @var array $panel Panel data
     */
    public array $panel = [];

    /**
     * @var array $plugin Plugin settings
     */
    public array $plugin = [];
    
    /**
     * @var array $navbar Navbar data
     */
    public array $navbar = [];

    /**
     * @var array $sidebar Sidebar data
     */
    public array $sidebar = [];

    /**
     * @var array $breadcrumb Breadcrumb data
     */
    public array $breadcrumb = [];

    /**
     * @var array $pagination Pagination data
     */
    public array $pagination = [];

    /**
     * Adds data to page
     *
     * @param array|string $data
     * @param mixed $value
     * 
     * @return void
     */
    public function data( array|string $data, mixed $value = null )
    {
        if (is_string($data)) {
            $this->data[$data] = $value;
            return;
        }
        $this->data = array_merge($this->data, $data);
    }
}
