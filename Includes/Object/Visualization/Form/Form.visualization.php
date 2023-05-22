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

namespace App\Visualization\Form;

/**
 * Form
 */
class Form extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to language
     */
    protected array $translate = [
        'body.?.body.?.data.desc',
        'body.?.body.?.data.title',
        'body.?.data.button.?.value',
        'body.?.data.button.?.data.value',
        'body.?.body.?.body.?.data.placeholder',
        'body.?.body.?.body.?.data.title',
        'body.?.body.?.body.?.data.desc',
        'body.?.body.?.body.?.data.button',
        'body.?.body.?.body.?.data.empty',
        'body.?.body.?.body.?.body.?.data.label'
    ];

    /**
     * @var array $defaultValues List of keys and their default values
     */
    protected array $defaultValues = [];

    /**
     * @var array $parseToPath List of keys which their values will be parsed to path
     */
    protected array $parseToPath = [
        'body.?.body.?.options.template.body',
        'body.?.body.?.body.?.options.template.root',
        'body.?.body.?.body.?.options.template.body',
        'body.?.body.?.body.?.options.template.option',
        'body.?.body.?.body.?.options.template.text',
        'body.?.body.?.body.?.body.?.options.template.option'
    ];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [
        'body.?.body.?.body.?.data.href'
    ];

    /**
     * @var array $link List of links
     */
    public array $link = [
        'href'
    ];

    /**
     * @var array $dataAssign List of data insert variables
     */
    public array $dataAssign = [
        'href'
    ];

    /**
     * @var array $type Input types
     */
    private array $type = [
        'id' => 'Id',
        'date'=> 'Date',
        'icon' => 'Icon',
        'text' => 'Text',
        'file' => 'File',
        'user' => 'User',
        'time' => 'Time',
        'html'  => 'Textarea',
        'color' => 'Color',
        'field' => 'Field',
        'email' => 'Text',
        'radio' => 'Radio',
        'number' => 'Text',
        'select' => 'Select',
        'select[]' => 'Select',
        'button' => 'Button',
        'username' => 'Text',
        'password' => 'Password',
        'textarea' => 'Textarea',
        'checkbox' => 'Checkbox',
        'checkbox[]' => 'Checkbox',

        'file/image[]' => 'File',
        'file/image' => 'File',
        'file/misc[]' => 'File',
        'file/misc' => 'File',
        'file/zip[]' => 'File',
        'file/zip' => 'File',
    ];

    /**
     * @var bool $allowButtons If true - under form will be displayed buttons
     */
    private bool $allowButtons = true;

    public function disButtons()
    {
        $this->delete('data.button');

        return $this;
    }

    /**
     * Adds data to form
     *
     * @param  array $data
     * 
     * @return $this
     */
    public function data( array $data )
    {
        $this->obj->set('data.fill', $data);

        return $this;
    }

    /**
     * Sets event on success
     *
     * @param  object $page Page object
     * @param  string $method Method name
     * 
     * @return void
     */
    public function callOnSuccess( object $page, string $method )
    {
        $this->obj->set('data.button.method', [
            'type' => 'hidden',
            'name' => 'method',
            'value' => $method
        ]);
        $this->obj->set('options.success.page', $page);
        $this->obj->set('options.success.method', $method);

        return $this;
    }

    /**
     * Sets position to form
     *
     * @param  string $name Form name
     * @param  callable $function Function
     * 
     * @return $this
     */
    public function form( string $name, callable $function = null )
    {
        $this->elm1($name, $function);

        return $this;
    }

    /**
     * Sets position to frame in form
     *
     * @param  string $name Frame name
     * @param  callable $function Function
     * 
     * @return $this
     */
    public function frame( string $name, callable $function = null )
    {
        $this->elm2($name, $function);

        return $this;
    }

    /**
     * Sets position to input in frame
     *
     * @param  string $name Frame name
     * @param  callable $function Function 
     * 
     * @return $this
     */
    public function input( string $name, callable $function = null )
    {
        $this->elm3($name, $function);

        return $this;
    }

    /**
     * Marks current input as required
     * 
     * @return $this
     */
    public function require()
    {
        $this->set('options.required', true);

        return $this;
    }

    protected function clb_each_elm1()
    {
        $fill = $this->get('data.fill'); 
        if (!$this->get('data.fill'))
        {
            return;
        }

        
        foreach ($this->get('body') as $name => $data)
        {
            $this->elm2($name);
            
            foreach ($this->get('body') as $_name => $_data)
            {
                $this->elm3($_name);

                if ($this->get('options.type') == 'password')
                {
                    $this->up();
                    continue;
                }
                
                if ($this->get('data.value'))
                {
                    $this->up();
                    continue;
                }

                if (!isset($fill[$_name]))
                {
                    $this->up();
                    continue;
                }

                $data = $fill[$_name];

                switch ($this->obj->get('options.type'))
                {
                    case 'text':
                    case 'html':
                    case 'date':
                    case 'color':
                    case 'field':
                    case 'email':
                    case 'textarea':
                        $this->obj->set('data.value', $data);
                    break;

                    case 'number':
                        $this->obj->set('data.value', (int)$data);
                    break;

                    default:
                    
                        if ($this->obj->get('body') === false)
                        {
                            $this->obj->set('data.value', $data);
                        }
                    break;

                    case 'checkbox[]':
                    case 'checkbox':
                    case 'select':
                    case 'select[]':
                    case 'radio':
                
                        foreach ($this->get('body') as $__name => $__data)
                        {
                            $this->elm4($__name);
                            
                            if (is_array($data))
                            {
                                if (in_array($this->get('data.value'), $data))
                                {
                                    $this->set('options.checked', true);
                                }
                                $this->up();
                                continue;
                            }
                            
                            if ($this->get('data.value') == $data)
                            {
                                $this->set('options.checked', true);
                            }

                            $this->up();
                        }

                    break;
                }

                $this->up();
            }

            $this->up();
        }
    }

    /**
     * Function which will be executed on every second nested object
     * 
     * @return void
     */
    protected function clb_each_elm3()
    {
        // Type of input is not set
        if (!in_array($this->obj->get('options.type'), array_keys($this->type)))
        {
            if (!$this->obj->get('options.template.root'))
            {
                if (!$this->obj->get('options.template.body'))
                {
                    if (!$this->obj->get('options.template.option'))
                    {
                        $this->obj->delete();
                        return;
                    }
                }
            }
        }

        // Set template to input
        if (!$this->obj->get('options.template.option'))
        {
            if (!$this->obj->get('options.template.root'))
            {   
                if (!$this->obj->get('options.template.body'))
                {   
                    $this->obj->set('options.template.option', '/Includes/Object/Visualization/Form/Templates/Type/' . $this->type[$this->obj->get('options.type')] . '.phtml');
                }
            }
        }
    }
}