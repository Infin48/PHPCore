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
 * Terms
 */
class Terms extends Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Terms.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // Language
        $language = $data->get('inst.language');

        // If registration isn't allowed
        if ($system->get('registration_enabled') == 0)
        {
            // Show error page
            $this->error404();
        }
        
        $data->set('data.links', []);
        $data->set('data.links.register', '<a href="' . $this->url->build('/register/')  . '">' . $language->get('L_REGISTER.L_NO') . '</a>');
        $data->set('data.links.login', '<a href="' . $this->url->build('/login/')  . '">' . $language->get('L_REGISTER.L_ALREADY') . '</a>');
    }
}