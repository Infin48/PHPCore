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

namespace App\Page\Get;

/**
 * Content
 */
class Content extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        $post = new \App\Model\Post();

        $data->set('data', array_merge($data->get('data'), $post->get()));

        $this->data->d = $data->d = $data->d->getDataToGenerate();
        $this->language = $data->get('inst.language');
        
        require $post->get('url');

        exit();
    }
}