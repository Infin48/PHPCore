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
        return (PAGE - 1) * (int)$this->pagination['max'];
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

        if (PAGE > $ceil or PAGE < 1) {
            if (PAGE < 1) redirect($this->pagination['url'] . 'page-1/');
            redirect($this->pagination['url'] . 'page-' . $ceil . '/');
        }

        for ($i = 1; $i <= $ceil; $i++) {

            $page = $url = $i;

            if ($ceil > 8 and PAGE >= 6 and $i === 2) {
                $page = '...';
                $i = PAGE - 3;
                $url = $i - 1;
            }

            if ($ceil > 8 and PAGE <= $ceil - 5 and $i === PAGE + 3) {
                $page = '...';

                $i = $ceil - 1;
                $url += 1;
            }

            $paginationData[] = [
                'page' => $page,
                'url' => $this->pagination['url'] . 'page-' . $url . '/'
            ];

        }

        return [
            'data' => $paginationData,
            'url' => $this->pagination['url'],
            'next' => PAGE != ceil($this->pagination['total'] / $this->pagination['max']) ? true : false,
            'previous' => PAGE != 1 ? true : false,
            'enabled' => $this->pagination['total'] > $this->pagination['max'] ? true : false,
            'max' => $this->pagination['max'],
            'offset' => (PAGE - 1) * (int)$this->pagination['max']
        ];
    }
}