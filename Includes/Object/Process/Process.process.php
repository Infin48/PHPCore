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

use Model\Url;
use Model\Form;
use Model\Session;

use Process\ProcessCheck;
use Process\ProcessHTMLConfiguration;

/**
 * Process
 */
class Process
{
    /**
     * @var array $permission Process permission
     */
    public static array $permission = [
        '/Admin/Category/Create' => 'admin.forum',
        '/Admin/Category/Delete' => 'admin.forum',
        '/Admin/Category/Down' => 'admin.forum',
        '/Admin/Category/Edit' => 'admin.forum',
        '/Admin/Category/Permission' => 'admin.forum',
        '/Admin/Category/Up' => 'admin.forum',
        '/Admin/Deleted/Post/Back' => 'admin.forum',
        '/Admin/Deleted/Post/Delete' => 'admin.forum',
        '/Admin/Deleted/Topic/Back' => 'admin.forum',
        '/Admin/Deleted/Topic/Delete' => 'admin.forum',
        '/Admin/Deleted/ProfilePost/Back' => 'admin.forum',
        '/Admin/Deleted/ProfilePost/Delete' => 'admin.forum',
        '/Admin/Deleted/ProfilePostComment/Back' => 'admin.forum',
        '/Admin/Deleted/ProfilePostComment/Delete' => 'admin.forum',
        '/Admin/Forum/Create' => 'admin.forum',
        '/Admin/Forum/Delete' => 'admin.forum',
        '/Admin/Forum/Down' => 'admin.forum',
        '/Admin/Forum/Edit' => 'admin.forum',
        '/Admin/Forum/Permission' => 'admin.forum',
        '/Admin/Forum/Up' => 'admin.forum',
        '/Admin/Group/Create' => 'admin.group',
        '/Admin/Group/Delete' => 'admin.group',
        '/Admin/Group/Down' => 'admin.group',
        '/Admin/Group/Edit' => 'admin.group',
        '/Admin/Group/Permission' => 'admin.group',
        '/Admin/Group/Up' => 'admin.group',
        '/Admin/Label/Create' => 'admin.label',
        '/Admin/Label/Delete' => 'admin.label',
        '/Admin/Label/Down' => 'admin.label',
        '/Admin/Label/Edit' => 'admin.label',
        '/Admin/Label/Up' => 'admin.label',
        '/Admin/Menu/Button/Create' => 'admin.menu',
        '/Admin/Menu/Button/Delete' => 'admin.menu',
        '/Admin/Menu/Button/Down' => 'admin.menu',
        '/Admin/Menu/Button/Edit' => 'admin.menu',
        '/Admin/Menu/Button/Up' => 'admin.menu',
        '/Admin/Menu/ButtonSub/Create' => 'admin.menu',
        '/Admin/Menu/ButtonSub/Delete' => 'admin.menu',
        '/Admin/Menu/ButtonSub/Down' => 'admin.menu',
        '/Admin/Menu/ButtonSub/Edit' => 'admin.menu',
        '/Admin/Menu/ButtonSub/Up' => 'admin.menu',
        '/Admin/Menu/Dropdown/Create' => 'admin.menu',
        '/Admin/Menu/Dropdown/Edit' => 'admin.menu',
        '/Admin/Notification/Create' => 'admin.notification',
        '/Admin/Notification/Delete' => 'admin.notification',
        '/Admin/Notification/Down' => 'admin.notification',
        '/Admin/Notification/Edit' => 'admin.notification',
        '/Admin/Notification/Up' => 'admin.notification',
        '/Admin/Page/Create' => 'admin.page',
        '/Admin/Page/Delete' => 'admin.page',
        '/Admin/Page/Edit' => 'admin.page',
        '/Admin/Plugin/Delete' => 'admin.settings',
        '/Admin/Plugin/Install' => 'admin.settings',
        '/Admin/Plugin/Uninstall' => 'admin.settings',
        '/Admin/Report/Close' => 'admin.forum',
        '/Admin/Settings/Language/Activate' => 'admin.settings',
        '/Admin/Settings/Language/Delete' => 'admin.settings',
        '/Admin/Settings/URL/Create' => 'admin.settings',
        '/Admin/Settings/URL/Delete' => 'admin.settings',
        '/Admin/Settings/Email' => 'admin.settings',
        '/Admin/Settings/EmailSend' => 'admin.settings',
        '/Admin/Settings/Index' => 'admin.settings',
        '/Admin/Settings/Other' => 'admin.settings',
        '/Admin/Settings/Registration' => 'admin.settings',
        '/Admin/Synchronize/Scripts' => 'admin.?',
        '/Admin/Synchronize/Styles' => 'admin.?',
        '/Admin/Template/Activate' => 'admin.template',
        '/Admin/Template/ClosePreview' => 'admin.template',
        '/Admin/Template/Delete' => 'admin.template',
        '/Admin/Template/Preview' => 'admin.template',
        '/Admin/User/Activate' => 'admin.user',
        '/Admin/User/Delete' => 'admin.user',
        '/Admin/User/Edit' => 'admin.user',
        '/Admin/User/Promote' => 'admin.user',
        '/Admin/User/Search' => 'admin.user',
        '/Admin/Optimize' => 'admin.?',
        '/Post/Create' => 'post.create',
        '/Post/Delete' => 'post.delete',
        '/Post/Edit' => 'post.edit',
        '/ProfilePost/Create' => 'profilepost.create',
        '/ProfilePost/Delete' => 'profilepost.delete',
        '/ProfilePost/Edit' => 'profilepost.edit',
        '/ProfilePostComment/Create' => 'profilepost.create',
        '/ProfilePostComment/Delete' => 'profilepost.delete',
        '/ProfilePostComment/Edit' => 'profilepost.edit',
        '/Topic/Create' => 'topic.create',
        '/Topic/Delete' => 'topic.delete',
        '/Topic/Edit' => 'topic.edit',
        '/Topic/Label' => 'topic.label',
        '/Topic/Lock' => 'topic.lock',
        '/Topic/Move' => 'topic.move',
        '/Topic/Stick' => 'topic.stick',
        '/Topic/Unlock' => 'topic.lock',
        '/Topic/Unstick' => 'topic.stick',
    ];

    /**
     * @var array $key Process keys
     */
    public static array $key = [
        '/Admin/Category/Delete' => 'category_id',
        '/Admin/Category/Down' => 'category_id',
        '/Admin/Category/Edit' => 'category_id',
        '/Admin/Category/Permission' => 'category_id',
        '/Admin/Category/Up' => 'category_id',
        '/Admin/Deleted/Post/Back' => 'deleted_id',
        '/Admin/Deleted/Post/Delete' => 'deleted_id',
        '/Admin/Deleted/Topic/Back' => 'deleted_id',
        '/Admin/Deleted/Topic/Delete' => 'deleted_id',
        '/Admin/Deleted/ProfilePost/Back' => 'deleted_id',
        '/Admin/Deleted/ProfilePost/Delete' => 'deleted_id',
        '/Admin/Deleted/ProfilePostComment/Back' => 'deleted_id',
        '/Admin/Deleted/ProfilePostComment/Delete' => 'deleted_id',
        '/Admin/Forum/Create' => 'category_id',
        '/Admin/Forum/Delete' => 'forum_id',
        '/Admin/Forum/Down' => 'forum_id',
        '/Admin/Forum/Edit' => 'forum_id',
        '/Admin/Forum/Permission' => 'forum_id',
        '/Admin/Forum/Up' => 'forum_id',
        '/Admin/Group/Delete' => 'group_id',
        '/Admin/Group/Down' => 'group_id',
        '/Admin/Group/Edit' => 'group_id',
        '/Admin/Group/Permission' => 'group_id',
        '/Admin/Group/Up' => 'group_id',
        '/Admin/Label/Delete' => 'label_id',
        '/Admin/Label/Down' => 'label_id',
        '/Admin/Label/Edit' => 'label_id',
        '/Admin/Label/Up' => 'label_id',
        '/Admin/Menu/Button/Delete' => 'button_id',
        '/Admin/Menu/Button/Down' => 'button_id',
        '/Admin/Menu/Button/Edit' => 'button_id',
        '/Admin/Menu/Button/Up' => 'button_id',
        '/Admin/Menu/ButtonSub/Create' => 'button_id',
        '/Admin/Menu/ButtonSub/Delete' => 'button_sub_id',
        '/Admin/Menu/ButtonSub/Down' => 'button_sub_id',
        '/Admin/Menu/ButtonSub/Edit' => 'button_sub_id',
        '/Admin/Menu/ButtonSub/Up' => 'button_sub_id',
        '/Admin/Menu/Dropdown/Edit' => 'button_id',
        '/Admin/Notification/Delete' => 'notification_id',
        '/Admin/Notification/Down' => 'notification_id',
        '/Admin/Notification/Edit' => 'notification_id',
        '/Admin/Notification/Up' => 'notification_id',
        '/Admin/Page/Delete' => 'page_id',
        '/Admin/Page/Edit' => 'page_id',
        '/Admin/Plugin/Delete' => 'plugin_name_folder',
        '/Admin/Plugin/Install' => 'plugin_name_folder',
        '/Admin/Plugin/Uninstall' => 'plugin_id',
        '/Admin/Report/Close' => 'report_id',
        '/Admin/Settings/Language/Activate' => 'language_name_folder',
        '/Admin/Settings/Language/Delete' => 'language_name_folder',
        '/Admin/Settings/URL/Delete' => 'settings_url_id',
        '/Admin/Template/Activate' => 'template_name_folder',
        '/Admin/Template/Delete' => 'template_name_folder',
        '/Admin/Template/Preview' => 'template_name_folder',
        '/Admin/User/Activate' => 'user_id',
        '/Admin/User/Delete' => 'user_id',
        '/Admin/User/Edit' => 'user_id',
        '/Admin/User/Promote' => 'user_id',
        '/Conversation/Edit' => 'conversation_id',
        '/Conversation/Leave' => 'conversation_id',
        '/Conversation/Mark' => 'conversation_id',
        '/Conversation/Recipient' => 'conversation_id',
        '/ConversationMessage/Create' => 'conversation_id',
        '/ConversationMessage/Edit' => 'conversation_message_id',
        '/Post/Create' => 'topic_id',
        '/Post/Delete' => 'post_id',
        '/Post/Edit' => 'post_id',
        '/Post/Like' => 'post_id',
        '/Post/Report' => 'report_type_id',
        '/Post/Unlike' => 'post_id',
        '/ProfilePost/Create' => 'user_id',
        '/ProfilePost/Delete' => 'profile_post_id',
        '/ProfilePost/Edit' => 'profile_post_id',
        '/ProfilePost/Report' => 'report_type_id',
        '/ProfilePostComment/Create' => 'profile_post_id',
        '/ProfilePostComment/Delete' => 'profile_post_comment_id',
        '/ProfilePostComment/Edit' => 'profile_post_comment_id',
        '/ProfilePostComment/Report' => 'report_type_id',
        '/Topic/Like' => 'topic_id',
        '/Topic/Report' => 'report_type_id',
        '/Topic/Unlike' => 'topic_id',
        '/Topic/Lock' => 'topic_id',
        '/Topic/Stick' => 'topic_id',
        '/Topic/Unlock' => 'topic_id',
        '/Topic/Unstick' => 'topic_id',
        '/Topic/Delete' => 'topic_id'
    ];

    /**
     * @var string $redirectURL Url where user will be redirected after process execution
     */
    private string $redirectURL = '';

    /**
     * @var string $message Process message
     */
    private string $message = '';

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
     * @var string|int $processKey Process key
     */
    private string|int $processKey;

    /**
     * @var bool $refresh Refresh
     */
    private bool $refresh = false;
    
    /**
     * @var string $process Name of process
     */
    private string $process;

    /**
     * @var int $id Return ID
     */
    private int $id = 0;

    /**
     * @var \Model\Form $form Form
     */
    private \Model\Form $form;
    
    /**
     * @var object $perm Permission
     */
    public \Model\Permission $perm;

    /**
     * @var object $system System
     */
    public \Model\System $system;

    /**
     * @var object $check ProcessCheck
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
     * Assigns key to process
     * 
     * @param string $process Process
     * @param string $key Process key
     *
     * @return void
     */
    public static function addKey( string $process, string $key ) {
        self::$key[$process] = $key;
    } 

    /**
     * Assigns required permission to process
     * 
     * @param string $process Process
     * @param string $permission Process permission
     *
     * @return void
     */
    public static function addPerm( string $process, string $permission ) {
        self::$permission[$process] = $permission;
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
     * Returns refresh value
     *
     * @return bool
     */
    public function getRefresh()
    {
        return $this->refresh;
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
    private function checkData( array $format = [] )
    {
        $formData = $this->form->getData();

        foreach ($format as $input => $settings) {

            foreach ($settings as $key => $value) {
                
                switch ($key) {

                    case 'required':

                        if (empty($formData[$input]) or (is_string($formData[$input]) and strip_tags($formData[$input], ['img']) === '')) {
                            throw new \Exception\Notice($input);
                        }
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
                            throw new \Exception\Notice($this->process);
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
                                $formData[$input] = $this->purifier->purify($formData[$input]);
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
     * @param  string|int $key Process key
     * @param  string $mode 'direct' - Doesn't redirect user after process executing, 'silent' - Doesn't show error messages, 'normal' - Default mode
     * @param  array $data Additional process data
     *
     * @return bool|void If is enabled 'direct' mode, returns boolean otherwise user will be automatically redirected to set URL.
     */
    public function form( string $type, string $on = 'submit', string $url = null, string|int $key = null, string $mode = 'normal', array $data = [] )
    {
        $this->{$mode}();

        $this->form = new Form($this->mode === 'direct' ? true : false);
        
        // IF SUBMIT BUTTON WAS PRESSED
        if ($this->form->isSend($on)) {

            $this->redirectURL = $url ?? $this->redirectURL;
            $this->data = $data;

            if ($key) {
                $this->processKey = $key;
            }

            $process = $this->explode($type);

            foreach ($data['options']['input'] ?? [] as $inputName => $value) {
                $process->require['form'][$inputName]['custom'] = $value;
            }

            $HTML = new ProcessHTMLConfiguration($process->HTML ?? 'default');
            $this->purifier = $HTML->get();

    
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
     * @param  string|int $key Process key
     * @param  bool $on If true - process will be executed
     * @param  array $data Additional process data
     * 
     * @return bool|void If is enabled 'direct' mode, returns boolean otherwise user will be automatically redirected to set URL.
     */
    public function call( string $type, string $url = null, string $mode = 'normal', string|int $key = null, bool $on = true, array $data = [] )
    {
        if ($on !== true) {
            return false;
        }

        if ($key) {
            $this->processKey = $key;
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
        $ex = array_filter(explode('/', $type));

        $this->message = $type;
        
        unset($this->data['options']);

        // PROCESS KEY
        if (isset($this->processKey)) {

            if (!isset(self::$key[$type])) {
                throw new \Exception\System('ID pro proces \'' . $type . '\' nebyl nalezen!');
            }

            $this->data[self::$key[$type]] = $this->processKey;
        }
        
        $process = 'Process\\' . implode('\\', $ex);
        
        if (str_starts_with($type, '$')) {

            if ($this->perm->has('admin.settings') === false) {
                $this->redirect();
            }

            array_shift($ex);
            
            $type = '/Plugin/' . implode('/' , $ex);
            $process = 'Process\Plugin\\' . array_shift($ex) . '\\' . implode('\\', $ex);
        }
        
        // SET VARIABLES
        $this->process = $type;

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
        redirect(Url::build($this->redirectURL));
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

            $block = $process->options['verify']['block'];
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

            $this->id = $process->getID() ?: 0;
            if (($process->options['success'] ?? SUCCESS_SESSION) === SUCCESS_SESSION) {
                Session::put('success', $this->message);
            }

            switch ($this->mode) {

                case 'direct':
                    $this->redirectURL = $process->redirectURL ?: '';
                    if ($process->refresh === true) {
                        $this->refresh = true;
                    } 
                    return true;
                break;

                default:
                    $this->redirectURL = $process->redirectURL ?: $this->redirectURL;

                    $page = PAGE;
                    if (is_array(PAGE)) {

                        foreach (PAGE as $key => $value) {
                            $page = '.' . $key . $value;
                        }
                        $page = substr($page, 1);
                    }


                    $this->redirectURL .= !TAB ? '' : '/tab-' . TAB . '/';
                    $this->redirectURL .= PAGE == 1 ? '' : '/page-' . $page . '/';
                    
                    $this->redirect();
                break;
            }
        }

        if ($this->mode === 'silent') {
            $this->redirect();
        }
        throw new \Exception\Notice($this->message);
        return false;
    }
}