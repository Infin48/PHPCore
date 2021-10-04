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
 * Pagination
 */
class Pagination
{
    /**
     * @var array $pagination Pagination data
     */
    private array $pagination = ['enabled' => false, 'url' => ''];

    /**
     * @var array $label Pagination label
     */
    private string $label;

    /**
     * @var int $page PPage number
     */
    private int $page;
    
    /**
     * Constructor
     *
     * @param  string $label Pagination label
     */
    public function __construct( string $label = '' )
    {
        $this->label = $label;

        if ($this->label) {
            $this->page = PAGE[$this->label] ?? 1;
        } else {
            $this->page = PAGE;
        }
    }

    /**
     * Sets max objects on one page
     *
     * @param  int $max
     * 
     * @return void
     */
    public function max( int $max )
    {
        $this->pagination['max'] = $max;

        if (isset($this->pagination['total'])) {
            $this->pagination['enabled'] = true;
        }
    }
    
    /**
     * Sets total amount of objects
     *
     * @param  int $total
     * 
     * @return void
     */
    public function total( int $total )
    {
        $this->pagination['total'] = $total;

        if (isset($this->pagination['max'])) {
            $this->pagination['enabled'] = true;
        }
    }
    
    /**
     * Sets url
     *
     * @param  string $url
     * 
     * @return void
     */
    public function url( string $url )
    {
        $this->pagination['url'] = $url;
    }
    
    /**
     * Returns offset
     *
     * @return int
     */
    public function getOffset()
    {
        return ($this->page - 1) * (int)$this->pagination['max'];
    }
    
    /**
     * Returns max objects on one page
     *
     * @return int
     */
    public function getMax()
    {
        return (int)$this->pagination['max'];
    }
    
    /**
     * Returns pagination data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->pagination['total']) === false or isset($this->pagination['max']) === false) return false;

        $ceil = ceil($this->pagination['total'] / $this->pagination['max']);
        $ceil = ($ceil == 0) ? 1 : $ceil;

        
        $_url = '';
        if (is_array(PAGE) and $this->label) {
            
            foreach (PAGE as $key => $value) {
                
                if ($key != $this->label) {
                    $_url .= $key . $value . '.';
                }
            }
        }

        $tab = '';
        if (TAB) {
            $tab = 'tab-' . TAB . '/';
        }
        
        if ($this->page > $ceil or $this->page < 1) {
            if ($this->page < 1) redirect($this->pagination['url'] . 'page-' . $_url . $this->label . '1/');
            redirect($this->pagination['url'] . 'page-' . $_url . $this->label . $ceil . '/');
        }

        for ($i = 1; $i <= $ceil; $i++) {

            $page = $url = $i;

            if ($ceil > 8 and $this->page >= 6 and $i === 2) {
                $page = '...';
                $i = $this->page - 3;
                $url = $i - 1;
            }

            if ($ceil > 8 and $this->page <= $ceil - 5 and $i === $this->page + 3) {
                $page = '...';

                $i = $ceil - 1;
                $url += 1;
            }

            $paginationData[] = [
                'page' => $page,
                'url' => $this->pagination['url'] . 'page-' . $_url . $this->label . $url . '/' . $tab,
                'active' => $this->page == $url ? true : false
            ];
        }


        return [
            'data' => $paginationData,

            'url' => $this->pagination['url'] . '/' . $tab,
            'urlNext' => $this->pagination['url'] . '/page-' . $_url . $this->label . ($this->page + 1) . '/' . $tab,
            'urlPrevious' => $this->pagination['url'] . '/page-' . $_url . $this->label . ($this->page - 1) . '/' . $tab,

            'next' => $this->page != ceil($this->pagination['total'] / $this->pagination['max']) ? true : false,
            'previous' => $this->page != 1 ? true : false,
            'enabled' => $this->pagination['total'] > $this->pagination['max'] ? true : false,
            'max' => $this->pagination['max'],
            'offset' => ($this->page - 1) * (int)$this->pagination['max']
        ];
    }
}