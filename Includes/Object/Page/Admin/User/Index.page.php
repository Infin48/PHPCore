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

namespace App\Page\Admin\User;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.user';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');
        
        // If static mode is enabled
		if ($system->get('site.mode') == 'static')
		{
            // Show error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('users')->elm2('user')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/User.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(20);
        $pagination->total($db->select('app.user.count()'));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // List
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/User/Index.json');
        $form
            ->form('user')
                ->callOnSuccess($this, 'searchUser');
        $data->form = $form->getDataToGenerate();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/User.json');
        $list->elm1('user')->fill(data: $db->select('app.user.all()'), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list )
        {
            // If logged user has higher group index then current user
            // Show button o edit user
            if ($list->get('data.group_index') < LOGGED_USER_GROUP_INDEX or $list->get('data.user_id') == LOGGED_USER_ID)
            {
                $list
                    ->show('data.button.edit')
                    ->set('data.button.edit.href', '/admin/user/show/' . $list->get('data.user_id'));
            }
        });
        $data->list = $list->getDataToGenerate();
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function searchUser( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $user = $db->select('app.user.byName()', $post->get('user_name'));

        if (!$user)
        {
            throw new \App\Exception\Notice('user_name_does_not_exist');
        }

        // Redirect to user page
        $data->set('data.redirect', '/admin/user/show/' . $user['user_id']);
    }
}