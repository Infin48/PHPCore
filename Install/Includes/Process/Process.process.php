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

namespace Process;

use Model\Form;

/**
 * Process
 */
class Process
{
    /**
     * @var array $data Data
     */
    private array $data = [];

    /**
     * @var \Model\Form $form Form
     */
    private \Model\Form $form;

    /**
     * @var \Process\ProcessCheck $check ProcessCheck
     */
    private \Process\ProcessCheck $check;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->check = new ProcessCheck();
    }

    /**
     * Checks form data
     * 
     * @param array $format Process form data
     * 
     * @throws \Exception\Notice If is found any error in data
     *
     * @return bool
     */
    private function checkData( array $format )
    {
        $formData = $this->form->getData();

        foreach ($format as $input => $settings) {

            if (isset($settings['required'])) {
                if (empty($formData[$input])) {
                    throw new \Exception\Notice($input);
                }
            }

            foreach ($settings as $key => $value) {
                
                switch ($key) {


                    case 'length_max':
                        $this->check->maxLength($formData[$input], $input, $value);
                    break;


                    case 'length_min':
                        $this->check->minLength($formData[$input], $input, $value);
                    break;


                    case 'type':


                        if (!isset($formData[$input])) {
                            switch ($value) {

                                case 'array':
                                    $formData[$input] = [];
                                break;


                                case 'text':
                                case 'email':
                                case 'username':
                                case 'password':
                                    $formData[$input] = '';
                                break;


                                case 'radio':
                                case 'number':
                                case 'checkbox':
                                    $formData[$input] = 0;
                                break;
                            }
                        }


                        switch ($value) {

                            case 'array':
                                $formData[$input] = is_array($formData[$input]) ? $formData[$input] : [];
                            break;


                            case 'text':
                            case 'email':
                            case 'username':
                            case 'password':
                                $formData[$input] = is_string($formData[$input]) ? $formData[$input] : '';
                            break;


                            case 'radio':
                            case 'checkbox':
                                $formData[$input] = $formData[$input] == 1 ? 1 : 0;
                            break;


                            case 'number':
                                $formData[$input] = ctype_digit($formData[$input]) ? $formData[$input] : 0;
                            break;
                        }


                        switch ($value) {


                            case 'text':
                            case 'email':
                            case 'username':
                            case 'password':
                                $formData[$input] = strip_tags($formData[$input]);
                            break;
                        }


                        if (!empty($formData[$input])) {
                            switch ($value) {


                                case 'email':
                                    $this->check->email($formData[$input]);
                                break;


                                case 'username':
                                    $this->check->userName($formData[$input]);
                                break;


                                case 'password':
                                    $this->check->password($formData[$input]);
                                break;
                            }
                        }

                        $this->data[$input] = $formData[$input];
                    break;
                }
            }
        }

        return true;
    }

    /**
     * Starts process on form submitting
     *
     * @param  string $type Path to process
     * @param  string $on Name of submit button
     * @param  array $data Additional process data
     *
     * @return void
     */
    public function form( string $type, string $on = 'submit', array $data = [] )
    {
        // LOAD FORM
        $this->form = new Form();
        
        // IF SUBMIT BUTTON WAS PRESSED
        if ($this->form->isSend($on)) {

            // EXPLODE PROCESS NAME
            $ex = array_filter(explode('/', $type));


            $process = 'Process\\' . implode('\\', $ex);
            $process =  new $process();
    
            if ($this->checkData($process->require['form'] ?? [])) {

                // CHECK INPUTS
                $process->data(array_merge($data, $this->data));
                if ($process->process() !== false) {
                    $this->redirect();
                    return true;
                }

                $this->redirect();
                return false;
            }


            return false;
        }
    }

    /**
     * Redirects users
     *
     * @return void
     */
    private function redirect()
    {
        redirect('/Install/');
    }
}