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
 * Users
 */
class Users extends Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Users.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // If profiles are disabled
        if ($system->get('site.mode.blog.profiles') == 0)
        {
            // Show 404 error page
            $this->error404();
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Users.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_USERS);
        $pagination->url($this->url->getURL());
        $pagination->total($db->select('app.user.count()'));
        $data->pagination = $pagination->getData();

        // List
        $list = new \App\Visualization\Lists\Lists('Root/Lists:/Formats/Users.json');

        // Fill list with registered users
        $list->elm1('users')->fill(data: $db->select('app.user.all()'), function: function ( \App\Visualization\Lists\Lists $list ) use ($system)
        {
            // Define variables
            $list
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $list->get('data')))
                // date.group = User's group label
                ->set('data.group', $this->build->user->group(data: $list->get('data')))
                // data.date = Date of user registered
                ->set('data.date', $this->build->date->short($list->get('data.user_registered')))
                // date.user_image = User's profile image
                ->set('data.user_image', $this->build->user->image(data: $list->get('data'), role: true));

            // If blog mode is disabled
            if ($system->get('site.mode') != 'blog')
            {
                // data.reputation - User's reputation label
                $list->set('data.reputation', $this->build->user->reputation($list->get('data.user_reputation')));
            }
        });

        // Save list and get ready to generate
        $data->list = $list->getDataToGenerate();
    }
}