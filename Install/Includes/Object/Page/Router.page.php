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

namespace App\Page;

/**
 * Router
 */
class Router extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = '/Body.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database $db )
    {
        $JSON = new \App\Model\JSON('/Install/Includes/Settings.json');

        // Set default language
        $language = $data->get('inst.language');
        $language->load( language: $JSON->get('language') ?: 'cs' );
        
        if (!is_readable(ROOT . '/Install/Includes/Settings.json') or !is_writable(ROOT . '/Install/Includes/Settings.json'))
        {
            throw new \App\Exception\System('Aplikace vyžaduje oprávnění číst a zapisovat do souboru \'/Install/Includes/Settings.json\'');
        }

        $URLs = $URLsl = array_values(array_filter(explode('/', $_SERVER['REQUEST_URI'])));
        $URLs = array_map('ucfirst', $URLs);
        if (!$URLs)
        {
            $URLs = $URLsl = ['Index'];
        }

        $URL = '\App\Page\\' . implode('\\', $URLs);
        if (!file_exists(ROOT . INCLUDES . '/Page/' . implode('/', $URLs) . '.page.php'))
        {
            $URL = '\App\Page\Index';
        }

        $data->set('data.page', mb_strtolower(implode('-', array_slice(array_filter(explode('\\', $URL)), 2))));
        $data->set('data.back', $JSON->get('back'));
        $data->set('data.operation', $JSON->get('operation'));
        $data->set('data.previous', match (mb_strtolower(implode('-', $URLsl)))
        {
            'menu' => '/',
            'admin' => '/database/',
            'settings' => '/admin/',
            default => '/menu/',
        });

        $page = new $URL(
            db: $db,
            data: $data
        );
        // Page
        $page->body( $data, $db );

        $page->checkFormSubmit();

        new \App\Style\Style(
            data: $data
        );
    }
}