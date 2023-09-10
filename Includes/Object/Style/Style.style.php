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

namespace App\Style;

/**
 * Style
 */
class Style
{
    /**
     * @var array $templates List of templates
     */
    private static array $templates = ['shift'];

    /**
     * @var bool $e404 If true - 404 Error page will be displayed
     */
    public bool $e404 = false;

    /**
     * @var string $URL Page URL
     */
    public string $URL = '/';

    /**
     * @var string $URLcurrent Current page URL
     */
    public string $URLcurrent = '/';

    /**
     * @var \App\Model\Url $url Url
     */
    private \App\Model\Url $url;

    /**
     * @var \App\Model\User $user User
     */
    private \App\Model\User $user;

    /**
     * @var \App\Model\Post $post Post data
     */
    private \App\Model\Post $post;

    /**
     * @var \App\Model\Data $data Data
     */
    private \App\Model\Data $data;

    /**
     * @var \stdClass $build Builder
     */
    private \stdClass $build;

    /**
     * @var \App\Model\System $system System
     */
    private \App\Model\System $system;

    /**
     * @var \App\Plugin\Plugin $plugin Plugin
     */
    private \App\Plugin\Plugin $plugin;

    /**
     * @var \App\Model\Template $template Template
     */
    private \App\Model\Template $template;

    /**
     * @var \App\Model\Language $language Language
     */
    private \App\Model\Language $language;

    /**
     * @var \App\Model\Permission $permission Permission
     */
    private \App\Model\Permission $permission;
    
    /**
     * Constructor
     *
     * @param  \Model\Data $data
     */
    public function __construct( \App\Model\Data $data, \App\Model\Url $url, \stdClass $build, bool $e404 = false )
    {
        $this->e404 = $e404;

        // Builders
        $this->build = $build;
        
        $this->url = $url;
        $this->path = new \App\Model\Path();
        $this->user = $data->get('inst.user');
        $this->post = new  \App\Model\Post();
        $this->system = $data->get('inst.system');
        $this->plugin = $data->get('inst.plugin');
        $this->template = new \App\Model\Template();
        $this->language = $data->get('inst.language');
        $this->permission = $this->user->get('permission');
        
        if (isset(self::$templates[2]))
        {
            if (isset($this->template->get('body')[self::$templates[2] ?? '']))
            {
                self::$templates[1] = $this->template->get('body')[self::$templates[2]];
            }

            if (self::$templates[2] == 'Root/Style:/Templates/Plugin.phtml')
            {
                if (isset($this->template->get('body')[self::$templates[3] ?? '']))
                {
                    self::$templates[1] = $this->template->get('body')[self::$templates[3]];
                    unset(self::$templates[2]);
                    array_values(self::$templates);
                }
            }
        }
        
        $data->set('data.head.title', str_replace('"', '&quot;', $data->get('data.head.title')));

        // Truncate description
        $data->set('data.head.description', str_replace('"', '&quot;', truncate(strip_tags($data->get('data.head.description'), 250))));
        
        if ($this->e404 == true)
        {
            // Page title
            $data->set('data.head.title', $this->language->get('L_ERROR'));
        }
        
        $data->d = $data->d->getDataToGenerate();

        $this->data = $data;
    }
    
    /**
     * Sets next template
     *
     * @param  string $template Template name
     * 
     * @return void
     */
    public static function setTemplate( string $template )
    {
        array_push(self::$templates, $template);
    }
	
	/**
     * Changes page body
     *
     * @param  string $body Body
     * 
     * @return void
     */
    public static function changeBody( string $body )
    {
		self::$templates[1] = $body;
    }
    
    /**
     * Shows template
     *
     * @return void
     */
    public function show()
    {
        $url = $this->url;
        $path = $this->path;
        $post = $this->post;
        $build = $this->build;
        $plugin = $this->plugin;
        $system = $this->system;
        $language = $this->language;
        $template = $this->template;

        if ($this->e404 == true)
        {
            require $path->build('Root/Style:/Templates/Error.phtml');

            exit();
        }
        array_shift(self::$templates);
        
        require $path->build(self::$templates[0]);
    }
}