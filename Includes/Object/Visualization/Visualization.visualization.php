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

use Model\Url;
use Model\Template;
use Model\System;

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
     * @var \Model\System $system System
     */
    protected \Model\System $system;
    
    /**
     * @var \Model\Template $template Template
     */
    protected \Model\Template $template;

    /**
     * @var \Visualization\VisualizationObject $obj VisualizationObject
     */
    public \Visualization\VisualizationObject $obj;

    /**
     * @var string $default Default object
     */
    private string $default = 'default';
    
    /**
     * @var array $nest Nesting array
     */
    private array $nest = [];
    
    /**
     * @var int $i Loop counting
     */
    protected int $i = 0;

    /**
     * Constructor
     *
     * @param  string|array $format Path to format
     */
    public function __construct( string|array $format )
    {
        $this->system = new System();
        $this->template = new Template();

        $t = array_filter(explode('\\', get_class($this)));
        $this->visualization = $t[count($t) - 1];

        $this->templatePath = '/Includes/Object/Visualization/' . $this->visualization . '/Templates';
        if (is_array($format)) {
            $this->object = $format;
        } else {
            
            $this->object = $this->getFormat($format);
        }

        $this->obj = new VisualizationObject($this->object);
    }

    /**
     * Returns format
     *
     * @param string $format Path to format
     * 
     * @return void
     */
    private function getFormat( string $format )
    {
        $_t = explode('\\', get_class($this));
        array_shift($_t);
        array_pop($_t);

        $path = ROOT . '/Includes/Object/Visualization/' . implode('/', $_t) . '/Formats' . $format . '.json';
        if (str_starts_with($format, '$')) {
                
            $ex = array_filter(explode('/', mb_substr($format, 1)));
            $path = ROOT . '/Plugins/' . array_shift($ex) . '/Object/Visualization/' . implode('/', $_t) . '/' . implode('/', $ex) .  '.json';
        }

        if (file_exists($path) === false) {
            throw new \Exception\System('Hledaný formát \'' . $path . '\' nebyl nalezen!');
        }
        
        return json_decode(file_get_contents($path), true);
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
     * Sets ID
     *
     * @param int $ID The ID
     * 
     * @return $this
     */
    public function id( int $ID )
    {   
        $this->obj->set->options('id', $ID);

        return $this;
    }

    /**
     * Sets type
     *
     * @param string $type The type
     * 
     * @return $this
     */
    public function type( string $type )
    {   
        $this->obj->set->options('type', $type);

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
        $this->obj->delete->body($object);
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
            $this->obj->delete->button();
            return $this;
        }
    
        $this->obj->delete->button($button);

        return $this;
    }
    
    /**
     * Deletes default object in current object
     *
     * @return $this
     */
    public function deleteDefaultObject()
    {
        $this->obj->delete->body($this->default);

        return $this;
    }

    /**
     * Converts data
     * 
     * @param array $convert Convert data
     * 
     * @return $this
     */
    public function convert( array $convert = null )
    {
        if (is_null($convert)) {

            $this->up()->down($this->default);
            $convert = $this->obj->get->data('convert') ?: [];
            $this->up()->down($this->lastInsertName());
        }

        foreach ($convert as $to => $from) {
            
            if (is_array($from)) {
                continue;
            }
            
            if (in_array($to, ['title', 'desc'])) {
                $this->obj->set->data($to, '$' . $this->obj->get->data($from));
                continue;
            }
            $this->obj->set->data($to, $this->obj->get->data($from));
        }

        $this->obj->delete->data('convert');
    }

    /**
     * Appends another object to current object
     *
     * @param  array $data Object data
     * @param  callable $function The function
     * @param  int $i Number of object
     * @param  int $count Number of objects
     * 
     * @return $this
     */
    public function appTo( array $data, string $default = 'default', callable $function = null, int $i = 1, int $count = 1 )
    {
        $this->default = $default;

        if (count($this->list) === 3) {
            $this->row($this->list[1]);
        }

        $this->previousFillName = $this->nest[array_key_last($this->nest)] ?? $default;

        $this->lastInsertName = mt_rand();
        $this->obj->set->body->after($this->previousFillName, [$this->lastInsertName => $this->obj->get->body($default)]);
        
        $this->down($this->lastInsertName);
        
        $this->obj->set->data(array_merge($this->obj->get->data(), $data));
        $this->convert($this->obj->get->data('convert'));
        

        if (($data['checked'] ?? false) === true) {
            $this->obj->set->options('checked', true);
        }

        if (empty($this->nest) === false) {
            $this->nest[array_key_last($this->nest)] = $this->lastInsertName;
        }
        
        $_i = $this->i;
        if ($function) {
            $function($this, $this->i, $count);
        }
        
        $this->i = $_i;
        $this->i++;

        $this->up();

        $this->clb('appTo');

        return $this;
    }
    
    /**
     * Adds to current object body another objects 
     *
     * @param  array $data Objects data
     * @param string $default Name of object
     * @param  callable $function The Function
     * 
     * @return $this
     */
    public function fill( array $data, string $default = 'default', callable $function = null )
    {
        $this->i = 1;

        array_push($this->nest, $default);
        foreach ($data as $row) {
            $this->appTo(data: $row, default: $default, function: $function, i: $this->i, count: count($data));
        }
        array_pop($this->nest);

        $this->obj->delete->body($default);

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
        // DELETE HIDDEN OBJECTS
        if ($visual->obj->get->options('hide') === true) {
            $visual->obj->delete->delete();
            return false;
        }

        if ($name === 'default') {
            $visual->obj->delete->delete();
            return false;
        }

        // MOVE BOTTOM ROWS FROM BODY TO BOTTOM
        if ($visual->obj->is->body('bottom')) {
            $cache = $visual->obj->get->body('bottom');
            $visual->obj->delete->body('bottom');
            $visual->obj->set->body('bottom', $cache);
        }

        if ($string = $visual->obj->get->data('href')) {
            foreach ($visual->obj->get->data() as $key => $value) {
                if (!is_array($value)) {
                    $string = strtr($string, ['{' . $key . '}' => $value]);
                }
            }

            switch (substr($string, 0, 1)) {
            
                case '$':
                    $string = substr($string, 1);
                break;

                case '~':
                    $string = Url::build(substr($string, 1));
                break;

                default:
                    $string = Url::build(Url::getURL() . $string);
                break;
            }


            $visual->obj->set->data('href', $string);
        }
        
        foreach (array_filter((array)$visual->obj->get->options('template')) as $name => $template) {

            switch (substr($template, 0, 1)) {

                // LOAD TEMPLATE FROM ROOT
                case '~':
                    $path = ROOT . substr($template, 1);
                break;

                // LOAD TEMPLATE FROM VISUALIZATION TEMPLATE
                case '%':
                    $path = ROOT . $visual->templatePath . substr($template, 1);
                break;

                // LOAD TEMPLATE FROM PLUGIN
                case '$':
                    $path = $visual->template->template($template);
                break;

                // LOAD TEMPLATE FROM DEFAULT STYLE
                default:
                    if (str_contains($template, ROOT)) {
                        $path = $template;
                        break;
                    }

                    $path = $visual->template->template('/Blocks/Visualization/' . $visual->visualization . $template);
                break;
            }

            if (file_exists($path) === false) {
                throw new \Exception\System($visual->visualization . ' vyžaduje šablonu ' . $path);
            }       

            if (is_string($name)) {
                $visual->obj->set->template($name, $path);
            } else {
                $visual->obj->set->options('template', $path);
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
            $visual->obj->delete->delete();
        }
    }

    /**
     * Creates object in current relay
     *
     * @param  string $objectName Name of new Object
     * @param string|int $previousObject Name  of previous object
     * @param string $pathToFormat
     * 
     * @return void
     */
    public function createObject( string $objectName, string|int $previousObject = null )
    {
        if (is_null($previousObject)) {
         
            $this->obj->set->body($objectName, [
                'options' => [],
                'data' => []
            ]);
        } else if ($previousObject === 1) {

            $this->obj->set->body([
                $objectName => [
                    'options' => [],
                    'data' => []
                ]
            ] + $this->obj->get->body());

        } else {
            
            $this->obj->set->body->before($previousObject, [
                $objectName => [
                    'options' => [],
                    'data' => []
                    ]
                ]
            );
        }
    }

    /**
     * Imports format before given object
     *
     * @param string $pathToFormat Path to format file
     * @param string $previousObject Name of previous object
     * 
     * @return void
     */
    public function import( string $pathToFormat, string $previousObject = null )
    {
        $object = $this->getFormat($pathToFormat)['body'];
        if (is_null($previousObject)) {
            
            $this->obj->set->body($this->obj->get->body() + $object);
            
        } else if ($previousObject == 1) {
            
            $this->obj->set->body($object + $this->obj->get->body());
            
        } else {
            
            $this->obj->set->body->before($previousObject, $object);
        }
    }

    /**
     * Returns position of searched object in current relay
     *
     * @param  string $objectName Name of searched Object
     * 
     * @return int
     */
    public function getPosition( string $objectName )
    {
        return $this->obj->get->position($objectName);
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

        if ($this->hideEmpty === true) {

            // REMOVE OBJECT WITH EMPTY BODY
            $this->each('empty');
        }

        $this->each('clb');

        // SYNC OBJECT
        $this->sync();

        // CALL CHILD FUNCTION
        $this->clb('getData');

        // SYNC OBJECT
        $this->sync();

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
     * @param callable $function The Function 
     * 
     * @return $this
     */
    public function object( string $objName, callable $function = null )
    {
        $this->update();
        
        $this->list = [0 => $objName];

        $this->obj = new VisualizationObject($this->object['body'][$objName] ?? []);

        if ($function) {
            $function($this);
        }

        return $this;
    }

    /**
     * Sets current row
     *
     * @param  string $rowName Row name
     * @param callable $function The Function 
     * 
     * @return $this
     */
    public function row( string $rowName, callable $function = null )
    {
        $this->update();
        $this->list = [
            0 => $this->list[0],
            1 => $rowName
        ];        
        $this->obj = new VisualizationObject($this->object['body'][$this->list[0]]['body'][$rowName] ?? []);

        if ($function) {
            $function($this);
        }

        return $this;
    }

    /**
     * Sets current option
     *
     * @param  string $optionName Option name
     * @param callable $function The Function 
     * 
     * @return $this
     */
    public function option( string $optionName, callable $function = null )
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

        if ($function) {
            $function($this);
        }

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
        if (count($this->list) >= 2) {
            $key = $this->list[count($this->list) - 2];
        }
        return match (count($this->list)) {
            0 => $this->sync(),
            1 => $this->sync(),
            2 => $this->object($key),
            3 => $this->row($key)
        };
    }

    /**
     * Moves one object down
     *
     * @return $this
     */
    public function down( string $object )
    {
        return match (count($this->list)) {
            0 => $this->object($object),
            1 => $this->row($object),
            2 => $this->option($object)
        };
    }
}