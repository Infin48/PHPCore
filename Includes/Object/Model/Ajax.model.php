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

use Model\Get;

/**
 * Ajax
 */
class Ajax 
{
    /**
     * @var \Model\Get $get Get
     */
    private \Model\Get $get;

    /**
     * @var \Process\Process $process Process
     */
    public \Process\Process $process;

    /**
     * @var array $data Data
     */
    public array $data = [
        'status' => false
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->get = new Get();

        if (!defined('AJAX')) {
            define('AJAX', true);
        }
    }

    /**
     * Ajax
     *
     * @param array $require
     * @param mixed $exec
     * 
     * @return void
     */
    public function ajax( array $require = null, mixed $exec )
    {
        if ($require) {
            foreach ($require as $item) {
                if (!$this->get->get($item)) {
                    exit();
                }
            }
        }

        if (is_callable($exec)) {
            $exec($this);
        }
    }
    
    /**
     * Checks if parametr in URL exists
     *
     * @param string $param
     * 
     * @return bool
     */
    public function is( string $param )
    {
        return $this->get->is($param);
    }

    /**
     * Returns URL parameter
     *
     * @param string $key Key
     * 
     * @return mixed
     */
    public function get( string $key )
    {
        return $this->get->get($key) ?: '';
    }

    /**
     * Sets redirect URL
     *
     * @param string $url URL
     * 
     * @return void
     */
    public function redirect( string $url )
    {
        $this->data['redirect'] = $url;
    }

    /**
     * Sets refresh
     * 
     * @return void
     */
    public function refresh()
    {
        $this->data['refresh'] = true;
    }

    /**
     * Sets process
     *
     * @param \Process\Process $process Process
     * @param \Model\Permission $permission Permission
     * @param string $type Process type
     * @param array $data Additional process data
     * @param string|int $key Process key
     * @param string $mode Process mode
     * @param string $method Process method
     * @param callable $success Function which will be executed after successfull process execution
     * @param callable $failure Function which will be executed after unsuccessfull process execution
     * 
     * @return void
     */
    public function process( \Process\Process $process, \Model\Permission $permission = null, string $type, array $data = [], string|int $key = '', string $mode = 'direct', string $method = 'call', callable $success = null, callable $failure = null )
    {
        $this->process = $process;

        if ($permission) {
            if (isset($this->process::$permission[$type]) and $permission->has($this->process::$permission[$type]) === false) {
                exit();
            }
        }

        if ($this->process->{$method}(type: $type, data: $data, mode: $mode, key: $key)) {
            $this->data['status'] = 'ok';

            if ($success) {

                if ($this->process->getURL()) {
                    $this->data['redirect'] = $this->process->getURL();
                }

                if ($this->process->getRefresh() === true) {
                    $this->data['refresh'] = true;
                }

                $success($this);
                
            }
        } else {
            if ($failure) {
                $failure($this);
            }
        }

    }

    /**
     * Sets ajax status to ok
     * 
     * @return void
     */
    public function ok()
    {
        $this->data['status'] = 'ok';
    }

    /**
     * Sets ajax status to false
     * 
     * @return void
     */
    public function false()
    {
        $this->data['status'] = false;
    }

    /**
     * Set ajax status to error
     * 
     * @return void
     */
    public function error()
    {
        $this->data['status'] = 'error';
    }

    /**
     * Set custom ajax status
     * 
     * @return void
     */
    public function status( string $status )
    {
        $this->data['status'] = $status;
    }

    /**
     * Adds data
     *
     * @param array $data
     * 
     * @return void
     */
    public function data( array $data )
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Ends ajax
     * 
     * @return void
     */
    public function end()
    {
        echo json_encode($this->data);
        exit();
    }
}
