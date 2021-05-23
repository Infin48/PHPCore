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
use Model\Session;
use Process\ProcessCheck;

/**
 * Process
 */
class Process {

    /**
     * @var string $redirectURL Url where user will be redirected after process execution
     */
    private string $redirectURL = '';

    /**
     * @var string $mode Mode type
     */
    private string $mode = 'normal';

    /**
     * @var array $data Process data
     */
    private array $data = [];
    
    /**
     * @var object $purifier Purifier class
     */
    private object $purifier;
    
    /**
     * @var string $block Block name
     */
    private string $block;
    
    /**
     * @var string $process Name of process
     */
    private string $process;

    /**
     * @var \Model\Form $form Form
     */
    private \Model\Form $form;
    
    /**
     * @var object $perm Permission
     */
    private \Model\Permission $perm;

    /**
     * @var object $system System
     */
    private \Model\System\System $system;

    /**
     * @var object $check ProcessCheck
     */
    private \Process\ProcessCheck $check;
    
    /**
     * Constructor
     */
    public function __construct( \Model\System\System $system, \Model\Permission $perm )
    { 
        $this->check = new ProcessCheck();

        $this->perm = $perm;
        $this->system = $system;
    }

    /**
     * Returns last inserted ID
     *
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }
    
    /**
     * Enables direct mode
     *
     * @return void
     */
    private function direct()
    {
        $this->mode = 'direct';
    }

    /**
     * Enables silent mode
     *
     * @return void
     */
    private function silent()
    {
        $this->mode = 'silent';
    }

    /**
     * Enables normal mode
     *
     * @return void
     */
    private function normal()
    {
        $this->mode = 'normal';
    }

    /**
     * Sets block name
     *
     * @return void
     */
    public function setBlock( string $block )
    {
        $this->block = $block;
    }

    /**
     * Sets default redirect url
     *
     * @return void
     */
    public function url( string $url )
    {
        $this->redirectURL = $url;
    }

    /**
     * Returns redirect url
     *
     * @return string
     */
    public function getURL()
    {
        return $this->redirectURL;
    }

    /**
     * Returns process path
     *
     * @return string
     */
    public function getProcess()
    {
        return $this->process;
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

        require ROOT . '/Assets/HTMLPurifier/HTMLPurifier.auto.php';
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'em,strong,del,pre,code');
        $config->set('HTML.AllowedAttributes', 'img.src,a.href,span.data-user');
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('span', 'data-user', 'Text');

        $this->purifier = new \HTMLPurifier($config);

        foreach ($format as $input => $settings) {

            foreach ($settings as $key => $value) {
                
                switch ($key) {

                    case 'required':
                        $formData[$input] or throw new \Exception\Notice($input);
                    break;

                    case 'function':
                        $value($formData[$input]) === true or throw new \Exception\Notice($input);
                    break;

                    case 'block':
                    case 'custom':
                    
                        if (empty($formData[$input]) and !isset($format[$input]['required'])) break;

                        $array = $value;
                        if ($key === 'block') {

                            $ex = explode('.', $value);
                            $array = (new $ex[0])->{$ex[1]}();
                        }

                        if (count(array_diff((array)$formData[$input], $array)) >= 1) {
                            return false;
                        }
                        $this->data[$input] = $formData[$input];
                    
                    break;

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
                                case 'html':
                                case 'clear':
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
                            case 'html':
                            case 'email':
                            case 'clear':
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

                            case 'array':
                                $formData[$input] = array_map('strip_tags', $formData[$input]);
                            break;

                            case 'text':
                            case 'email':
                            case 'username':
                            case 'password':
                                $formData[$input] = strip_tags($formData[$input]);
                            break;

                            case 'html':
                                $formData[$input] =  $this->purifier->purify($formData[$input]);
                            break;
                        }

                        if (!empty($formData[$input])) {
                            switch ($value) {

                                case 'email':
                                    $this->check->email($formData[$input], $input);
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
     * @param  string $url URL where user will be redirected after successfull process execution
     * @param  string $mode 'direct' - Doesn't redirect user after process executing, 'silent' - Doesn't show error messages, 'normal' - Default mode
     * @param  array $data Additional process data
     *
     * @return bool|void If is enabled 'direct' mode, returns boolean otherwise user will be automatically redirected to set URL.
     */
    public function form( string $type, string $on = 'submit', string $url = null, string $mode = 'normal', array $data = [] )
    {
        $this->{$mode}();

        $this->form = new Form($this->mode === 'direct' ? true : false);
        
        // IF SUBMIT BUTTON WAS PRESSED
        if ($this->form->isSend($on)) {

            $this->redirectURL = $url ?? $this->redirectURL;
            $this->data = $data;

            $process = $this->explode($type);

            foreach ($data['options']['input'] ?? [] as $inputName => $value) {
                $process->require['form'][$inputName]['custom'] = $value;
            }
    
            if (!isset($process->require['form']) or $this->checkData($process->require['form'] ?? [])) {

                return $this->_process($process);
            }

            return false;
        }
    }
    
    /**
     * Calls a process without submitting a form
     *
     * @param  string $type Path to process
     * @param  string $url URL where user will be redirected after successfull process execution
     * @param  string $mode 'direct' - Doesn't redirect user after process executing, 'silent' - Doesn't show error messages, 'normal' - Default mode
     * @param  bool $on If true - process will be executed
     * @param  array $data Additional process data
     * 
     * @return bool|void If is enabled 'direct' mode, returns boolean otherwise user will be automatically redirected to set URL.
     */
    public function call( string $type, string $url = null, string $mode = 'normal', bool $on = true, array $data = [] )
    {
        if ($on !== true) {
            return false;
        }

        $this->{$mode}();

        $this->redirectURL = $url ?? $this->redirectURL;
        $this->data = $data;

        return $this->_process($this->explode($type));
    }

    /**
     * Explodes process
     *
     * @param  string $type Process path
     * 
     * @return object
     */
    private function explode( string $type )
    {
        // EXPLODE PROCESS NAME
        $ex = explode('/', $type);

        // SET VARIABLES
        $this->process = $type;

        $this->id = $this->data[array_key_first($this->data)] ?? 0;

        unset($this->data['options']);

        $process = 'Process\\' . implode('\\', $ex);
        $process = new $process($this->process, $this->system, $this->perm);

        switch ($process->options['login'] ?? REQUIRE_LOGIN) {
            case REQUIRE_LOGOUT:
                if (LOGGED_USER_ID != 0) $this->redirect();
            break;
            case REQUIRE_LOGIN:
                if (LOGGED_USER_ID == 0) $this->redirect();
            break;
        }

        return $process;
    }

    /**
     * Redirects users
     *
     * @return void
     */
    private function redirect()
    {
        redirect($this->system->url->build($this->redirectURL));
    }

    /**
     * Ends process
     *
     * @param  object $process
     * 
     * @throws \Exception\Notice If is found any data error
     * @throws \Exception\System If is found internal error
     * 
     * @return bool|void
     */
    private function _process( object $process )
    {
        if (isset($process->options['verify'])) {

            $block = $this->block ?? $process->options['verify']['block'];
            $method = $process->options['verify']['method'];
            $selector = $process->options['verify']['selector'];

            $block = new $block;

            if (!$blockData = $block->{$method}($this->data[$selector])) {

                switch ($this->mode) {
                    case 'direct':
                        return false;
                    break;

                    case 'silent':
                        $this->redirect();
                    break;

                    default:
                        throw new \Exception\Notice($this->process);
                    break;
                }
            }

            foreach ($process->require['block'] ?? [] as $column) {
                $this->data[$column] = $blockData[$column] ?? '';
            }

        }

        foreach (array_filter(array_merge($process->require['block'] ?? [], $process->require['data'] ?? [])) as $input) {
            if (!isset($this->data[$input])) {
                throw new \Exception\System($this->process . ' | Vyžaduje \'' . $input . '\'');
            }
        }

        $process->data($this->data);
        if ($process->process() !== false) {

            $this->id = $process->getID();
            if (($process->options['success'] ?? SUCCESS_SESSION) === SUCCESS_SESSION) {
                Session::put('success', $this->process);
            }

            switch ($this->mode) {

                case 'direct':
                    $this->redirectURL = $process->redirectURL ?: '';
                    return true;
                break;

                default:
                    $this->redirectURL = $process->redirectURL ?: $this->redirectURL;
                    $this->redirectURL .= PAGE != 1 ? '/page-' . PAGE . '/' : '';
                    $this->redirect();
                break;
            }
        }

        if ($this->mode === 'silent') {
            $this->redirect();
        }
        throw new \Exception\Notice($this->process);
        return false;
    }
}