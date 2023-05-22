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

namespace App\Page\Install;

/**
 * Settings
 */
class Settings extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = '/Install/Settings.phtml';
    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database $db )
    {
        $form = new \App\Visualization\Form\Form('/Settings.json');
        $form->callOnSuccess($this, 'setupWebsiteSettings');
        $data->form = $form;
    }

    public function setupWebsiteSettings( \App\Model\Data $data, \App\Model\Database $db, \App\Model\Post $post )
    {
        $JSON = new \App\Model\JSON('/Install/Includes/Settings.json');
        $JSON->set('db', true);
        $JSON->save();
        
        $db->table(TABLE_SETTINGS, [
            'site.language' => $JSON->get('language'),
            'site.started' => DATE_DATABASE,
            'site.name' => $post->get('name'),
            'site.updated' => DATE_DATABASE,
            'site.description' => $post->get('description')
        ]);

        redirect('/install/end/');
    }
}