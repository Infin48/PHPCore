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

namespace App\Page\Admin;

/**
 * Router
 */
class Router extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Body.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.?';

    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    protected function ajax( string $ajax )
    {
        return match($ajax)
        {
            'window/delete-attachment',
            'window/notification/delete',
            'window/group/delete',
            'window/forum/delete',
            'window/category/delete',
            'window/label/delete',
            'window/role/delete',
            'window/sidebar-object/delete',
            'window/template/delete',
            'window/language/delete',
            'window/plugin/delete',
            'window/url/delete',
            'window/button/delete',
            'window/user/promote',
            'window/plugin/uninstall',
            'window/sub-button/delete',
            'window/deleted-post/delete' ,
            'window/deleted-profile-post/delete',
            'window/deleted-profile-post-comment/delete',
            'window/deleted-topic/delete',
            'window/user/delete',
            'window/page/delete' => 'window',

            'run/delete-attachment' => 'deleteAttachment',

            default => ''
        };
    }

    /**
     * Load data according to received ajax
     *
     * @param  string $ajax Received ajax
     * 
     * @return array Data
     */
    public function ajaxData( string $ajax )
    {
        return match($ajax)
        {
            'run/delete-attachment' => [
                'id' => STRING
            ],

            default => []
        };
    }

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // Put system model to file
        \App\Model\File\File::$system = $system;

        // Set default template
        $template = new \App\Model\Template(
            path: '/Includes/Admin/Styles',
            template: 'Default'
        );

        // Set default language
        $language = $data->get('inst.language');
        $language->load( language: $system->get('site.language'), template: $template, folder: 'admin' );

        // Put language to visualizators
        \App\Visualization\Visualization::$language = $language;

        // Default page title
        $data->set('data.head.title', $system->get('site.name'));

        // Default page description
        $data->set('data.head.description', $system->get('site.description'));

        // Set page favicon
        $favicon = '/Uploads/Site/PHPCore_icon.svg';
        if ($system->get('site.favicon'))
        {
            $favicon = '/Uploads/Site/Favicon.' . $system->get('site.favicon');
        }
        $data->set('data.head.favicon', $favicon);
        
        setlocale(LC_ALL, $system->get('site.locale').'.UTF-8');
        date_default_timezone_set($system->get('site.timezone'));

        // Check for ajax
        $this->checkForAjax();

        $page = $this->buildPage();

        // Load navbar
        $this->navbar = new \App\Visualization\Navbar\Navbar('Root/Navbar:/Formats/Admin.json');

        // Count of deleted content
        $deleted = $db->select('app.deleted.count()');

        // Save counts of reported content and unite with others
        $data->set('data.report-stats', $db->select('app.report.count()'));

        // If any content is reported
        if ($data->get('data.total') != 0)
        {
            $this->navbar->elm1('forum')->elm2('reported')
                ->set('data.notifiCount', $data->get('data.report-stats.total'))
                ->elm3('post')->set('data.notifiCount', $data->get('data.report-stats.post'))
                ->elm3('topic')->set('data.notifiCount', $data->get('data.report-stats.topic'))
                ->elm3('profilepost')->set('data.notifiCount', $data->get('data.report-stats.profile_post'))
                ->elm3('profilepostcomment')->set('data.notifiCount', $data->get('data.report-stats.profile_post_comment'));
        }

        // If any content is deleted
        if ($deleted != 0) {
            $this->navbar->elm1('forum')->elm2('deleted')->set('data.notifiCount', $deleted);
        }

        switch ($system->get('site.mode'))
        {
            // If blog mode is enabled
            case 'blog':

                $this->navbar
                    ->elm1('settings')
                        ->elm2('settings')
                            ->elm3('registration')->disable()
                    ->elm1('forum')
                        ->elm2('reported')->disable()
                        ->elm2('deleted')->disable()
                        ->elm2('forum')->disable()
                    ->elm1('other')
                        ->elm2('stats')->disable();

            break;

            // If static mode is enabled
            case 'static':

                $this->navbar
                    ->elm1('settings')
                        ->elm2('notification')->disable()
                        ->elm2('settings')
                            ->elm3('registration')->disable()
                    ->elm1('users')
                        ->elm2('group')->disable()
                        ->elm2('user')->disable()
                        ->elm2('role')->disable()
                    ->elm1('appearance')
                        ->elm2('sidebar')->disable()
                    ->elm1('forum')
                        ->elm2('reported')->disable()
                        ->elm2('deleted')->disable()
                        ->elm2('forum')->disable()
                        ->elm2('label')->disable()
                    ->elm1('other')
                        ->elm2('stats')->disable()
                        ->elm2('log')->disable()
                        ->elm2('other')->disable();

            break;
        }

        // If profiles are disabled
		if ($system->get('site.mode.blog.profiles') == 0)
		{
            $this->navbar->elm1('users')->elm2('role')->disable();
		}

        $page->navbar = $this->navbar;
        $page->body( $data, $db );

        // Check for ajax
        $page->checkForAjax();

        $data->navbar = $page->navbar->getDatatoGenerate();

        $this->checkFormSubmit();

        $this->end();
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
    public function window( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Language
        $language = $data->get('inst.language');

        return [
            'title' => $language->get('L_WINDOW.L_TITLE.L_CONFIRM'),
            'close' => $language->get('L_NO'),
            'submit' => $language->get('L_YES'),
            'content' => $language->get('L_WINDOW.L_DESC.' . $post->get('ajax'))
        ];
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
    public function deleteAttachment( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // File
        $file = new \App\Model\File\File();
       
        if (!$file->exists($post->get('id')))
        {
            return false;
        }
        
        $file->delete($post->get('id'));
    }
}