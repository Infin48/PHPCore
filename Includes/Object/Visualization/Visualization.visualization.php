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

use Model\Template;
use \Model\System\System;

/**
 * Visualization
 */
class Visualization
{
    /**
     * @var array $list Position
     */
    protected array $list = [];
    
    /**
     * @var array $object Object
     */
    protected array $object;
    
    /**
     * @var string $hideEmpty If true - rows and objects with empty body will be deleted
     */
    protected bool $hideEmpty = false;
    
    /**
     * @var \Model\System\System $system System
     */
    protected \Model\System\System $system;
    
    /**
     * @var \Model\Template $template Template
     */
    protected \Model\Template $template;

    /**
     * @var \Visualization\VisualizationObject $obj VisualizationObject
     */
    protected \Visualization\VisualizationObject $obj;

    /**
     * Constructor
     *
     * @param  string $format Path to format
     */
    public function __construct( string $format )
    {
        $this->system = new System();
        $this->template = new Template();

        $t = array_filter(explode('\\', get_class($this)));
        $this->visualization = $t[count($t) - 1];

        $this->templatePath = '/Includes/Object/Visualization/' . $this->visualization . '/Templates';
        $this->object = json_decode(file_get_contents(ROOT . '/Includes/Object/Visualization/' . $this->visualization . '/Formats/' . $format . '.json'), true);

        $this->obj = new VisualizationObject($this->object);
    }
    
    /**
     * Enables current object
     *
     * @return $this
     */
    public function enable()
    {   
        $this->obj->set->options('disabled', false);
        return $this;
    }

    /**
     * Disables current object
     *
     * @return $this
     */
    public function disable()
    {   
        $this->obj->set->options('disabled', true);

        return $this;
    }

    /**
     * Hides current object
     *
     * @return $this
     */
    public function hide()
    {   
        $this->obj->set->options('hide', true);
        return $this;
    }

    /**
     * Shows current object
     *
     * @return $this
     */
    public function show()
    {   
        $this->obj->set->options('hide', false);
        return $this;
    }

    /**
     * Sets value to current object options
     *
     * @param  string $key Option name
     * @param  string $value Option value
     * 
     * @return $this
     */
    public function setOptions( string $key, $value )
    {
        $this->obj->set->options($key, $value);
        return $this;
    }

    /**
     * Sets value to current object data
     *
     * @param  string $key Data name
     * @param  string $value Data value
     * 
     * @return $this
     */
    public function setData( string $key, $value )
    {   
        $this->obj->set->data($key, $value);
        return $this;
    }

    /**
     * Rows and objects with empty body will be deleted
     *
     * @return void
     */
    public function hideEmpty()
    {
        $this->hideEmpty = true;
    }

    /**
     * Selects current object
     *
     * @return $this
     */
    public function select()
    {   
        $this->obj->set->options('selected', true);
        return $this;
    }

    /**
     * Sets title to current object
     *
     * @param  string $title Title
     * 
     * @return $this
     */
    public function title( string $title )
    {
        $this->obj->set->data('title', $title);

        return $this;
    }

    /**
     * Sets description to current object
     *
     * @param  string $desc Description
     * 
     * @return $this
     */
    public function desc( string $desc )
    {
        $this->obj->set->data('desc', $desc);

        return $this;
    }

    /**
     * Sets value to current object data
     * 
     * @param mixed $value Value
     * 
     * @return $this
     */
    public function value( mixed $value )
    {
        $this->obj->set->data('value', $value);
        return $this;
    }

    /**
     * Jumps to latest created object
     *
     * @return $this
     */
    public function jumpTo()
    {
        match (count($this->list)) {
            0 => $this->object($this->lastInsertName()),
            1 => $this->row($this->lastInsertName()),
            2 => $this->option($this->lastInsertName())
        };
        return $this;
    }

    /**
     * Returns name of last created object
     *
     * @return string
     */
    public function lastInsertName()
    {
        return (string)$this->lastInsertName;
    }

    /**
     * Deletes object from body
     *
     * @param string $object Object name
     * 
     * @return $this
     */
    public function delete( string $object )
    {
        $this->obj->set->delete->body($object);
        return $this;
    }

    /**
     * Deletes button/s
     *
     * @param  string|array|null $button If null - deletes all buttons
     * 
     * @return $this
     */
    public function delButton( string|array $button = null )
    {
        if (is_array($button)) {
            foreach($button as $btn) {
                $this->delButton($btn);
            }
            return $this;
        }

        if (is_null($button)) {
            $this->obj->set->delete->button();
            return $this;
        }

        $this->obj->set->delete->button($button);
        return $this;
    }

    /**
     * Appends another object to current object
     *
     * @param  array $data Object data
     * 
     * @return $this
     */
    public function appTo( array $data )
    {
        if (count($this->list) === 3) {
            $this->row($this->list[1]);
        }

        foreach ($this->obj->get->convert() as $to => $from) {
            
            if (in_array($to, ['title', 'desc'])) {
                $data[$to] = '$' . $data[$from];
                continue;
            }
            
            $data[$to] = $data[$from] ?? '';
        }
        
        $_obj = $this->obj->get->body('default');
        if (isset($_obj['data']['convert'])) {
            unset($_obj['data']['convert']);
        }
        
        $_obj['data'] = array_merge($_obj['data'] ?? [], $data);
        
        $this->obj->set->body($this->lastInsertName = mt_rand(), $_obj);

        $this->clb('appTo');

        return $this;
    }
    
    /**
     * Adds to current object body another objects 
     *
     * @param  array $data Objects data
     * 
     * @return $this
     */
    public function fill( array $data )
    {
        foreach ($data as $row) {
            $this->appTo($row);
        }
        return $this;
    }
    
    /**
     * Calls 'clb' method in child class
     *
     * @param  string $methodName Method name
     * 
     * @return void
     */
    private function clb( string $methodName )
    {
        if (method_exists($this, 'clb_' . $methodName)) {
            $this->{'clb_' . $methodName}();
        }
    }
    
    /**
     * Executes basic code code for every object
     *
     * @param  \Visualization\Visualization $visual
     * @param  string $name Object name
     * 
     * @return void|false
     */
    public function each_ini( \Visualization\Visualization $visual, string $name )
    {
        // DELETE DEFAULT OBJECTS
        if ($name === 'default') {
            $this->obj->set->delete->delete();
            return false;
        }

        // DELETE HIDDEN OBJECTS
        if ($visual->obj->get->options('hide') === true) {
            $visual->obj->set->delete->delete();
            return false;
        }
        
        // MOVE BOTTOM ROWS FROM BODY TO BOTTOM
        if ($visual->obj->get->body('bottom')) {
            $cache = $visual->obj->get->body('bottom');
            $visual->obj->set->delete->body('bottom');
            $visual->obj->set->body('bottom', $cache);
        }

        foreach (array_filter((array)$visual->obj->get->options('template')) as $name => $template) {
                
            switch (substr($template, 0, 1)) {
                
                // LOAD TEMPLATE FROM ROOT
                case '~':
                    $visual->obj->set->template($name, $path = ROOT . substr($template, 1));
                break;

                // LOAD TEMPLATE FROM VISUALIZATION TEMPLATE
                case '$':
                    $visual->obj->set->template($name, $path = ROOT . $visual->templatePath . substr($template, 1));
                break;

                // LOAD TEMPLATE FROM DEFAULT STYLE TEMPLATE
                default:
                    $visual->obj->set->template($name, $path = $visual->template->template('/Blocks/' . $visual->visualization . $template));                    
                break;
            }
            
            if (file_exists($path) === false) {
                throw new \Exception\System($visual->visualization . ' vyžaduje šablonu ' . $path);
            }       
        }
    }
    
    /**
     * Removes objects with empty body
     *
     * @param  \Visualization\Visualization $visual
     * 
     * @return void
     */
    private function each_empty( \Visualization\Visualization $visual )
    {
        // DELETE OBJECT IF BODY IS EMPTY
        if (!$visual->obj->get->body() and $visual->obj->is->body()) {
            $visual->obj->set->delete->delete();
        }
    }

    /**
     * Returns object
     *
     * @return array
     */
    public function getData()
    {
        // SYNC OBJECT
        $this->sync();

        $this->each('ini');

        // SYNC OBJECT
        $this->sync();

        // IF IS ENABLED DELETEING OBJECTS WITH EMPTY BODY
        if ($this->hideEmpty === true) {

            // SET FUNCTION
            $this->each('empty');
        }

        $this->each('clb');

        // SYNC OBJECT
        $this->sync();

        // CALL CHILD FUNCTION
        $this->clb('getData');

        // SYNC OBJECT
        $this->sync();

        // RETURN OBJECT
        return $this->object;
    }
    
    /**
     * Calls 'each' method for every object
     *
     * @param  string $method Method name
     * 
     * @return void
     */
    protected function each( string $method )
    {
        $this->sync();

        if (method_exists($this, 'each_' . $method)) {
            
            foreach ($this->obj->get->body() as $object => $data) { $this->object($object);
                
                if ($this->{'each_' . $method}($this, $object) === false) {
                    continue;
                }

                foreach ($this->obj->get->body() as $row => $data) { $this->row($row);
                
                    if ($this->{'each_' . $method}($this, $row) === false) {
                        continue;
                    }

                    foreach ($this->obj->get->body() as $option => $data) { $this->option($option);
                
                        if ($this->{'each_' . $method}($this, $option) === false) {
                            continue;
                        }
                    }
                }
            }
        }
    }

    /**
     * Synchronise object
     *
     * @return $this
     */
    public function sync()
    {
        $this->update();

        $this->list = [];

        $this->obj = new VisualizationObject($this->object);

        return $this;
    }

    /**
     * Sets current object
     *
     * @param  string $objName Object name
     * 
     * @return $this
     */
    public function object( string $objName )
    {
        $this->update();
        
        $this->list = [0 => $objName];

        $this->obj = new VisualizationObject($this->object['body'][$objName] ?? []);

        return $this;
    }

    /**
     * Sets current row
     *
     * @param  string $rowName Row name
     * 
     * @return $this
     */
    public function row( string $rowName )
    {
        $this->update();
        $this->list = [
            0 => $this->list[0],
            1 => $rowName
        ];        
        $this->obj = new VisualizationObject($this->object['body'][$this->list[0]]['body'][$rowName] ?? []);

        return $this;
    }

    /**
     * Sets current option
     *
     * @param  string $optionName Option name
     * 
     * @return $this
     */
    public function option( string $optionName )
    {
        $this->update();
        $this->list = [
            0 => $this->list[0],
            1 => $this->list[1],
            2 => $optionName
        ];
        
        $this->obj = new VisualizationObject(
            $this->object['body'][$this->list[0]]['body'][$this->list[1]]['body'][$optionName] ?? []
        );

        return $this;
    }
    
    /**
     * Updates object
     *
     * @return void
     */
    private function update()
    {
        if (empty($this->obj->getObject())) {
            switch (count($this->list)) {
                case 0:
                    $this->object = [];
                break;
                case 1:
                    unset($this->object['body'][$this->list[0]]);
                break;
                case 2:
                    unset($this->object['body'][$this->list[0]]['body'][$this->list[1]]);
                break;
                case 3:
                    unset($this->object['body'][$this->list[0]]['body'][$this->list[1]]['body'][$this->list[2]]);
                break;
            }
            return;
        } 

        switch (count($this->list)) {
            case 0:
                $this->object = $this->obj->getObject();
            break;
            case 1:
                $this->object['body'][$this->list[0]] = $this->obj->getObject();
            break;
            case 2:
                $this->object['body'][$this->list[0]]['body'][$this->list[1]] = $this->obj->getObject();
            break;
            case 3:
                $this->object['body'][$this->list[0]]['body'][$this->list[1]]['body'][$this->list[2]] = $this->obj->getObject();
            break;
        }
    }

    /**
     * Moves one object up
     *
     * @return $this
     */
    public function up()
    {
        $key = $this->list[count($this->list) - 2];
        return match (count($this->list)) {
            0 => $this->sync(),
            1 => $this->sync(),
            2 => $this->object($key),
            3 => $this->row($key)
        };
    }
}