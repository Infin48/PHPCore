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

namespace App\Visualization;

use \App\Model\Url;

/**
 * Visualization
 */
class Visualization
{
    /**
     * @var array $list Position
     */
    public array $list = [];

    /**
     * @var array $object Object
     */
    public array $object = [];
    
    /**
     * @var array $cache Stored data in cache
     */
    private array $cache;

    /**
     * @var string $visualization Name of vizualizator
     */
    protected string $visualization = '';

    /**
     * @var string $visualization Name of vizualizator
     */
    protected string $templatePath = '';
    
    /**
     * @var string $hideEmpty If true - rows and objects with empty body will be deleted
     */
    protected bool $hideEmpty = false;

    /**
     * @var \App\Model\Path $path Path
     */
    protected \App\Model\Path $path;

    /**
     * @var \App\Visualization\VisualizationObject $obj VisualizationObject
     */
    public \App\Visualization\VisualizationObject $obj;

    /**
     * @var string $default Default object
     */
    private string $default = 'default';

    /**
     * @var \App\Model\Language $language Language instance
     */
    public static \App\Model\Language $language;
    
    /**
     * @var array $nest Nesting array
     */
    private array $nest = [];
    
    /**
     * @var int $i Loop counting
     */
    protected int $i = 0;

    /**
     * @var bool $empty If true - current row is empty
     */
    protected bool $empty = false;

    /**
     * Constructor
     *
     * @param  string|array $format Path to format
     */
    public function __construct( string|object|array $format = null, bool $includePlugins = true, string $plugin = '' )
    {
        // Models initialization
        $this->path = new \App\Model\Path();

        // IF format is not entered
        if (is_null($format))
        {
            $this->object = [];
            $this->obj = new VisualizationObject($this, '');

            return;
        }

        $explode = explode('\\', get_class($this));

        $this->visualization = array_pop($explode);
        $this->templatePath = '/Includes/Object/Visualization/' . $this->visualization . '/Templates';

        // If entered format is object or array
        if (is_object($format) or is_array($format))
        {
            $object = $format;
            if (is_object($format))
            {
                $object = $format->getObject();
            }

            $this->object = $object;
            $this->obj = new VisualizationObject($this, '');
            return;
        }
        $path = $this->path->build($format);

        $JSON = new \App\Model\File\JSON($path);
        if (!$JSON->exists())
        {
            throw new \App\Exception\System('Formát ' . $path . ' nebyl nalezen!');
        }
        
        $this->object = $JSON->get();
        $this->obj = new VisualizationObject($this, '');
    }

    protected function cache_set( string $key, mixed $value )
    {
        $this->cache[$key] = $value;
    }

    protected function cache_get( string $key )
    {
        return $this->cache[$key];
    }

    /**
     * Translate
     *
     * @param  string $key
     * 
     * @return void
     */
    private function translate( string $key )
    {
        $keys = explode('.', $key);
        if (in_array('?', $keys))
        {
            $position = array_search('?', $keys);
            
            foreach ($this->get(implode('.', array_slice($keys, 0, $position))) ?: [] as $_name => $body)
            {
                $keys[$position] = str_replace('.', '\.', $_name);
                $this->translate(implode('.', $keys));
            }
            
            return;
        }

        if (is_array($this->get($key)))
        {
            return;
        }

        if (str_starts_with($this->get($key), '$'))
        {
            $this->set($key, mb_substr($this->get($key), 1));
        }
        
        if ($trans = self::$language->get($this->get($key) ?: ''))
        {
            if (is_string($trans))
            {
                $this->set($key, $trans);
            }
        }
    }
    
    /**
     * Enables current object
     * 
     * @param  string $key The key
     *
     * @return $this
     */
    public function enable( string $key = null )
    {   
        if (is_null($key))
        {
            $this->set('options.disabled', false);
        }

        $this->set($key . '.disabled', false);

        return $this;
    }

    /**
     * Disables current object
     * 
     * @param  string $key The key
     *
     * @return $this
     */
    public function disable( string $key = null )
    {   
        if (is_null($key))
        {
            $this->set('options.disabled', true);
        }

        $this->set($key . '.disabled', true);

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
        $this->set('data.html.ajax-id', $ID);

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
        $this->set('options.type', $type);

        return $this;
    }

    /**
     * Hides current object
     * 
     * @param  string $key The key
     *
     * @return $this
     */
    public function hide( string $key = null )
    {   
        if (is_null($key))
        {
            $this->set('options.hide', true);
        }

        $this->set($key . '.hide', true);

        return $this;
    }

    /**
     * Shows current object or key
     * 
     * @param  string $key The key
     *
     * @return $this
     */
    public function show( string $key = null )
    {
        if (!is_null($key))
        {
            $this->obj->set($key . '.hide', false);
            return $this;
        }

        $this->obj->set('options.hide', false);

        return $this;
    }

    /**
     * Checks current row
     * 
     * @return $this
     */
    public function check()
    {   
        $this->set('options.checked', true);

        return $this;
    }

    /**
     * Selects current object
     *
     * @return $this
     */
    public function select()
    {   
        $this->set('options.selected', true);

        return $this;
    }

    /**
     * Sets value to current object data
     * 
     * @param string|int $value Value
     * 
     * @return $this
     */
    public function value( string|int $value )
    {
        $this->set('data.value', $value);

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
        $this->set('data.title', $title);

        return $this;
    }

    /**
     * Returns name of object
     *
     * @return void
     */
    public function getCurrentPositionName()
    {
        return $this->list[count($this->list) - 1] ?? '';
    }

    /**
     * Sets value to key
     *
     * @param  string|array $key Key
     * @param  mixed $value Value
     * 
     * @return $this
     */
    public function set( string|array $key, mixed $value = null )
    {
        $this->obj->set($key, $value);

        return $this;
    }

    /**
     * Move selected element after another
     *
     * @param  string $name Element name
     * 
     * @return $this
     */
    public function moveAfter( string $name )
    {
        $currentElementName = $this->list[count($this->list) - 1];
        $currentElementBody = $this->get();
        $this->up();
        $this->delete('body.' . $currentElementName);

        $key = array_search($name, array_keys($this->get('body')));
        $this->set('body',
            array_slice($this->get('body'), 0, $key + 1) + [$currentElementName => $currentElementBody] + array_slice($this->get('body'), $key + 1)
        );

        $this->down($currentElementName);

        return $this;
    }

    /**
     * Returns url
     *
     * @param  string $url URL
     * 
     * @return string
     */
    public function url( string $url )
    {
        return '$' . Url::build($url);
    }

    public function split( int ...$numbers )
    {
        $this->root();

        $result = 0;
        foreach ($numbers as &$number)
        {
            $number = $result += $number;
        }
        
        foreach ($numbers as $number)
        {
            $i = 1;
            foreach ($this->obj->get('body') as $name => $data)
            {
                $this->elm1($name);

                if ($i == $number)
                {
                    $this->obj->set('options.end', true);
                    $this->up();
                    break 1;
                }

                $i++;
            }
        }
    }

    /**
     * Appends visualization to current element
     *
     * @param  string $path Path
     * 
     * @return $this
     */
    public function append( string $path )
    {
        $path = $this->path->build($path);
        $JSON = new \App\Model\File\JSON($path);

        if (!$JSON->exists())
        {
            throw new \App\Exception\System('Formát ' . $path . ' nebyl nalezen!');
        }
        
        $this->set('body', array_merge($this->get('body'), $JSON->get('body')));

        return $this;
    }

    /**
     * Prepends object in current session
     *
     * @param  string $name Object name
     * 
     * @return $this
     */
    public function prepend( string $name = null )
    {
        $name ??= mt_rand();

        $array = [
            $name => [
                'data' => [],
                'options' => [],
                'body' => []
            ]
        ];

        $this->obj->set('body', $array + $this->obj->get('body'));

        $this->lastInsertName = $name;

        return $this;
    }

    /**
     * Creates object in current session
     *
     * @param  string $name Object name
     * 
     * @return $this
     */
    public function create( string $name = null )
    {
        $name ??= mt_rand();

        $this->set('body.' . $name, ['data' => [], 'options' => [], 'body' => []]);
        $this->lastInsertName = $name;
        
        return $this;
    }

    /**
     * Creates object in current session
     *
     * @param  string $name Object name
     * @param  string $newObjectName Name of new object
     * 
     * @return $this
     */
    public function createAfter( string $name, string $newObjectName = null )
    {
        $newObjectName ??= mt_rand();
        $this->lastInsertName = $newObjectName;
        
        $array = [
            $newObjectName => [
                'options' => [],
                'data' => [],
                'body' => []
            ]
        ];

        $key = array_search($name, array_keys($this->obj->get('body')));
        $this->set('body',
            array_slice($this->get('body'), 0, $key + 1) + $array + array_slice($this->get('body'), $key + 1)
        );

        return $this;
    }

    /**
     * Returns searched value from object
     *
     * @param  string $key Key
     * 
     * @return mixed
     */
    public function get( string $key = null, callable $function = null )
    {
        $return = $this->obj->get($key);
        
        if ($function) {
            if (is_array($return)) {
                
                foreach ($return as $key => $value) {
                    $function($this, $key, $value);
                }
            } else {
                $ex = explode('.', $key);
                
                $function($this, $ex[count($ex) - 1], $return);
            }

            return $this;
        }

        return $return;
    }

    /**
     * Returns count of elements
     *
     * @param  string $key Key
     * 
     * @return int
     */
    public function count( string $key )
    {
        return count($this->obj->get($key));
    }

    /**
     * Deletes given key
     *
     * @param  string|array $key Key
     * 
     * @return $this
     */
    public function delete( string|array $key = null )
    {
        $this->obj->delete($key);

        return $this;
    }

    /**
     * Translates value
     *
     * @param  string $value Text ot translate
     * 
     * @return string Translated text
     */
    public function toLang( string $value )
    {
        if (str_starts_with($value, '$')) {

            return substr($value, 1);
        }

        $keys = explode('.', $value);
        $return = self::$language;

        foreach ($keys as $key)
        {
            if (!isset($return[$key]))
            {
                return '';
            }

            $return = $return[$key];
        }

        return $return ?: $value;
    }

    /**
     * Jumps to latest created object
     *
     * @return $this
     */
    public function jumpTo()
    {
        $this->down($this->lastInsertName);
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

    public function assign( array $data, callable $function = null )
    {
        $this->obj->set('data', array_merge($this->get('data') ?: [], $data));
        $this->convert($this->get('data.convert') ?: []);
        
        if ($function)
        {
            $function($this);
        }

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

            $this->obj->up();
            $this->obj->down($this->default);
            $convert = $this->get('data.convert') ?: [];
            $this->obj->up();
            $this->obj->down($this->lastInsertName());
        }

        foreach ($convert as $to => $from) {
            
            if (is_array($from))
            {
                continue;
            }
            
            $this->set('data.' . $to, $this->get('data.' . $from));
        }

        $this->delete('data.convert');
    }

    /**
     * Appends another object to current object
     *
     * @param  array $data Object data
     * @param  string $default Default object name
     * @param  callable $function The function
     * @param  int $i Number of object
     * @param  int $count Number of objects
     * 
     * @return $this
     */
    public function appTo( array $data, string &$default = 'default', callable $function = null, int $i = 1, int $count = 1 )
    {
        if ($this->empty == true)
        {
            return $this;
        }
        
        $this->lastInsertName = mt_rand();

        if (!$this->get('body.default'))
        {
            $this->set('body.default', ['data' => [], 'options' => []]);
        }

        $this->obj->setAfter($default, $this->lastInsertName, $this->get('body.' . $this->default));

        $default =  $this->lastInsertName;
        $this->down($this->lastInsertName);
        
        $this->set('data', array_merge($this->get('data') ?: [], $data));
        $this->convert($this->get('data.convert') ?: []);
        
        if (($data['checked'] ?? false) === true)
        {
            $this->set('options.checked', true);
        }
        
        $return = true;
        if ($function)
        {
            $_i = $this->i;
            $return = $function($this, $this->i, $count);
            $this->i = $_i;
            if ($return === false)
            {
                $this->delete();
            }
        }
        
        if ($return !== false)
        {
            $this->i++;
        }
        
        $this->up();
        
        $this->clb('appTo');

        return $this;
    }
    
    /**
     * Adds to current object body another objects 
     *
     * @param  array $data Objects data
     * @param  string $default Name of object
     * @param  callable $function The Function
     * 
     * @return $this
     */
    public function fill( array $data, string $default = 'default', callable $function = null )
    {
        if ($this->empty == true)
        {
            return $this;
        }
        
        $list = $this->list;
        $this->default = $default;
        $this->i = 1;

        foreach ($data as $row)
        {
            $this->appTo(data: $row, default: $default, function: $function, i: $this->i, count: count($data));
        }

        $this->obj->delete('body.' . $this->default);

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
        if (method_exists($this, 'clb_' . $methodName))
        {
            $this->{'clb_' . $methodName}();
        }
    }

    /**
     * Executes basic code code for every object
     * 
     * @return void|false
     */
    public function each_ini()
    {
        if ($this->getCurrentPositionName() == 'default')
        {
            $this->delete();
            return false;
        }
        
        if ($this->visualization !== 'Form')
        {
            // Delete hidden objects
            if ($this->get('options.hide') === true)
            {
                $this->delete();
                return false;
            }
        }
    }

    /**
     * Returns object
     * 
     * @return array
     */
    public function getDataToGenerate()
    {
        // Sync object
        $this->root();
        
        $this->each('ini');

        // Sync object
        $this->root();
        
        $this->each_clb();
        
        // Sync object
        $this->root();
        
        // Call child function
        $this->clb('getData');
        
        // Sync object
        $this->root();

        
        if (isset($this->defaultValues))
        {
            foreach ($this->defaultValues as $key => $value)
            {
                $this->setDefaultValues($key, $value);
            }
        }
        
        $this->root();
        if (isset($this->parseToPath))
        {
            foreach ($this->parseToPath as $key )
            {
                $this->parseToPath($key);
            }
        }
        
        $this->root();

        if (isset($this->parseToURL))
        {
            foreach ($this->parseToURL as $key )
            {
                $this->parseToURL($key);
            }
        }
        
        $this->root();

        if (isset($this->translate))
        {
            foreach ($this->translate as $key)
            {
                $this->translate($key);
            }
        }
        
        // Sync object
        $this->root();
        
        return new \App\Visualization\VisualizationGenerate($this->object);
    }

    /**
     * Sets default value to given $key
     *
     * @param  string $key Path to key
     * @param  mixed $value Value to set 
     * 
     * @return void
     */
    private function setDefaultValues( string $key, mixed $value )
    {
        $keys = explode('.', $key);
        if (in_array('?', $keys))
        {
            $position = array_search('?', $keys);
            
            foreach ($this->get(implode('.', array_slice($keys, 0, $position))) ?: [] as $_name => $body)
            {
                $keys[$position] = str_replace('.', '\.', $_name);
                $this->setDefaultValues(implode('.', $keys), $value);
            }
            
            return;
        }
        if ($this->obj->get($key) == false)
        {
            $this->set($key, $value);
        }
    }

    /**
     * Sets default value to given $key
     *
     * @param  string $key Path to key
     * @param  mixed $value Value to set 
     * 
     * @return void
     */
    private function parseToURL( string $key )
    {
        $keys = explode('.', $key);
        if (in_array('?', $keys))
        {
            $position = array_search('?', $keys);
            
            foreach ($this->get(implode('.', array_slice($keys, 0, $position))) ?: [] as $_name => $body)
            {
                $keys[$position] = str_replace('.', '\.', $_name);
                $this->parseToURL(implode('.', $keys));
            }
            
            return;
        }
        if ($this->obj->get($key))
        {
            if (is_string($this->obj->get($key)))
            {
                $href = $this->obj->get($key);

                switch (substr($href, 0, 1))
                {                   
                    case '$':
                        $href = substr($href, 1);
                    break;

                    case '~':
                        $href = Url::build(Url::getURL() . substr($href, 1));
                    break;

                    default:

                        if (!str_starts_with($href, 'http://') and !str_starts_with($href, 'https://'))
                        {
                            if (!file_exists(ROOT . $href))
                            {
                                $href = Url::build($href);
                            }
                        }
                    break;
                }
                $this->set($key, $href);
            }
        }
    }

    /**
     * Sets default value to given $key
     *
     * @param  string $key Path to key
     * @param  mixed $value Value to set 
     * 
     * @return void
     */
    private function parseToPath( string $key )
    {
        $keys = explode('.', $key);
        if (in_array('?', $keys))
        {
            $position = array_search('?', $keys);
            
            foreach ($this->get(implode('.', array_slice($keys, 0, $position))) ?: [] as $_name => $body)
            {
                $keys[$position] = str_replace('.', '\.', $_name);
                $this->parseToPath(implode('.', $keys));
            }
            
            return;
        }
        if ($this->obj->get($key))
        {
            if (is_string($this->obj->get($key)))
            {
                $this->set($key, $this->path->build($this->obj->get($key)));
            }
        }
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
        $this->root();

        if (!method_exists($this, 'each_' . $method))
        {
            return;
        }

        $this->{'each_' . $method}($this, '');

        foreach ($this->get('body') as $object => $data) { $this->elm1($object);
            
            if ($this->{'each_' . $method}($this) === false)
            {
                continue;
            }
            
            foreach ($this->get('body') as $row => $data) { $this->elm2($row);
                
                if ($this->{'each_' . $method}($this) === false)
                {
                    continue;
                }

                foreach ($this->get('body') as $option => $data) { $this->elm3($option);
                    
                    if ($this->{'each_' . $method}($this) === false)
                    {
                        continue;
                    }

                    foreach ($this->get('body') as $option => $data) { $this->elm4($option);
                    
                        if ($this->{'each_' . $method}($this) === false)
                        {
                            continue;
                        }
                    }
                }
            }
        }

        $this->root();
    }

    private function each_clb()
    {
        $this->root();
        
        if (method_exists($this, 'clb_each'))
        {
            $this->clb_each();
        }
        foreach ($this->get('body') as $object => $data) { $this->elm1($object);
            
            if (method_exists($this, 'clb_each'))
            {
                $this->clb_each();
            }
            
            if (method_exists($this, 'clb_each_elm1'))
            {
                $this->clb_each_elm1();
            }
            
            foreach ($this->get('body') as $row => $data) { $this->elm2($row);

                if (method_exists($this, 'clb_each'))
                {
                    $this->clb_each();
                }

                if (method_exists($this, 'clb_each_elm2'))
                {
                    $this->clb_each_elm2();
                }

                foreach ($this->get('body') as $option => $data) { $this->elm3($option);

                    if (method_exists($this, 'clb_each'))
                    {
                        $this->clb_each();
                    }

                    if (method_exists($this, 'clb_each_elm3'))
                    {
                        $this->clb_each_elm3();
                    }

                    foreach ($this->get('body') as $option => $data) { $this->elm4($option);
                    
                        if (method_exists($this, 'clb_each'))
                        {
                            $this->clb_each();
                        }
    
                        if (method_exists($this, 'clb_each_elm4'))
                        {
                            $this->clb_each_elm4();
                        }
                    }
                }
            }
        }
        $this->root();
    }
    
    /**
     * Sorts objects by list
     *
     * @param  mixed $data Visualization Data
     * @param  mixed $sortBy List 
     * 
     * @return array
     */
    public function sort( array $sortBy )
    {
        $this->root();

        $body = $this->obj->get('body');
        $newBody = [];
        foreach ($sortBy as $object)
        {    
            if (isset($body[$object]))
            {
                $newBody[$object] = $body[$object];
            }
        }

        $this->obj->set('body', $newBody);
    }

    /**
     * Moves one object down in hierarchy
     *
     * @param  string $name Object name
     * 
     * @return $this
     */
    public function down( string $name )
    {
        if (count($this->list) == 4)
        {
            return;
        }
        array_push($this->list, $name);
        $this->obj = new \App\Visualization\VisualizationObject($this, 'body.' . implode('.body.', $this->list));

        return $this;
    }

    /**
     * Moves one object up in hierarchy
     * 
     * @return $this
     */
    public function up()
    {
        array_pop($this->list);
        
        $this->obj = new \App\Visualization\VisualizationObject($this, $this->list ? 'body.' . implode('.body.', $this->list) : '');

        return $this;
    }

    /**
     * Sets to first element in hierarchy
     *
     * @param  string $name Element name
     * @param  callable $function Function 
     * 
     * @return $this
     */
    public function elm1( string $name, callable $function = null )
    {
        $this->obj = new \App\Visualization\VisualizationObject($this, 'body.' . str_replace('.', '\.', $name));
        $this->list = [str_replace('.', '\.', $name)];

        if ($function)
        {
            $function($this);
        }

        return $this;
    }

    /**
     * Sets to second element in hierarchy
     *
     * @param  string $name Element name
     * @param  callable $function Function 
     * 
     * @return $this
     */
    public function elm2( string $name, callable $function = null )
    {
        $this->list = [$this->list[0], str_replace('.', '\.', $name)];
        $this->obj = new \App\Visualization\VisualizationObject($this, 'body.' . implode('.body.', $this->list));

        if ($function)
        {
            $function($this);
        }

        return $this;
    }

    /**
     * Sets to third element in hierarchy
     *
     * @param  string $name Element name
     * @param  callable $function Function 
     * 
     * @return $this
     */
    public function elm3( string $name, callable $function = null )
    {
        $this->list = [$this->list[0], $this->list[1], str_replace('.', '\.', $name)];

        $this->obj = new \App\Visualization\VisualizationObject($this, 'body.' . implode('.body.', $this->list));

        if ($function)
        {
            $function($this);
        }

        return $this;
    }

    /**
     * Sets to fourth element in hierarchy
     *
     * @param  string $name Element name
     * @param  callable $function Function 
     * 
     * @return $this
     */
    public function elm4( string $name, callable $function = null )
    {
        $this->list = [$this->list[0], $this->list[1], $this->list[2], str_replace('.', '\.', $name)];
        $this->obj = new \App\Visualization\VisualizationObject($this, 'body.' . implode('.body.', $this->list));

        if ($function)
        {
            $function($this);
        }

        return $this;
    }

    /**
     * Moves to root
     * 
     * @return $this
     */
    public function root()
    {
        $this->list = [];
        $this->obj = new \App\Visualization\VisualizationObject($this, '');
        
        return $this;
    }
}