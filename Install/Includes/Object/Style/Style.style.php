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
     * @var \App\Model\Post $post Post
     */
    public \App\Model\Post $post;

    /**
     * @var \App\Model\Data $data Data
     */
    public \App\Model\Data $data;

    /**
     * @var \App\Model\Language $language Language
     */
    public \App\Model\Language $language;
    
    /**
     * Constructor
     *
     * @param  \App\Model\System $system
     */
    public function __construct( \App\Model\Data $data, string $notice = '' )
    {
        $this->data = $data;

        $this->post = new \App\Model\Post();
        $this->language = $data->get('inst.language');

        if ($notice)
        {
            $data->set('data.notice', $notice);
        }

        $this->show();
    }
    
    /**
     * Sets next template
     *
     * @param  string $template Template name
     * 
     * @return void
     */
    public static function addTemplate( string $template )
    {
        array_push(self::$templates, $template);
    }
    
    /**
     * Shows template
     *
     * @return void
     */
    public function show()
    {
        array_shift(self::$templates);

        $post = $this->post;
        $data = $this->data;
        $language = $this->language;

        require ROOT . '/Install/Style/Templates' . self::$templates[0];
    }
}