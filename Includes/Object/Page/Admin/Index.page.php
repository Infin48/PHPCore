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
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Index.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.?';
    
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Navbar
        $this->navbar->elm1('dashboard')->elm2('index')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Index.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Save forum stats and unite with others
        $data->set('data.stats', $db->select('app.forum.stats()'));

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Index.json');
        $form
            ->form('info')
                ->disButtons()
                ->frame('info')
                    ->input('version')->value(PHPCORE_VERSION)
                    ->input('php')->value(phpversion())
                    ->input('database')->value($db->select('app.other.version()'))
                    ->input('started')->value($system->get('site_started'));
        $data->form = $form->getDataToGenerate();

        switch ($system->get('site_mode'))
        {
            case 'forum':
                // Block
                $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Index.json');
                $block->elm1('user')->value($data->get('data.stats.user'))
                    ->elm1('users')->value($db->select('app.user.recentCount()'))
                    ->elm1('topic')->value($data->get('data.stats.topic'))
                    ->elm1('post')->value($data->get('data.stats.post'));
                $data->block = $block->getDataToGenerate();

                $JSON = new \App\Model\File\JSON('http://api.phpcore.cz/novinky/');

                $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Index.json');
                $list->elm1('log')->show()->fill(data: $db->select('app.log.last()'));
                $list->elm1('news')->show()->fill(data: $JSON->get());
                $list->elm1('user')->show()->fill(data: $db->select('app.user.last()'), function: function ( \App\Visualization\ListsAdmin\ListsAdmin $list ) use ($permission)
                {
                    if ($list->get('data.group_index') < LOGGED_USER_GROUP_INDEX or $list->get('data.user_id') == LOGGED_USER_ID)
                    {
                        if ($permission->has('admin.user'))
                        {
                            $list->show('data.button.edit');
                        }
                    }
                });
                $list->split(1, 1, 1);
                $data->list = $list->getDataToGenerate();

                $statsDay = $db->select('app.chart.day()');

                // Generate array of last 30 days
                $dayDate = $dayDateTranslated = [];
                for ($i = 30; $i >= 0; $i--)
                {
                    array_push($dayDate, date('Y-m-d', strtotime('-' . $i . ' days')));
                    array_push($dayDateTranslated, mb_convert_case(strftime('%B %e, %Y', strtotime('-' . $i . ' days')), MB_CASE_TITLE));
                }

                $dayUsers = $dayPosts = $dayTopics = array_combine($dayDate, array_fill(0, count($dayDate), 0));
                
                // Fill days
                foreach ($statsDay as $value)
                {
                    foreach ($dayDate as $_date)
                    {
                        if ($value['day'] == $_date) {
                            $dayUsers[$_date] = $value['users'];
                            $dayPosts[$_date] = $value['posts'];
                            $dayTopics[$_date] = $value['topics'];
                            continue 2;
                        }
                    }
                }

                $data->chart = json_encode([
                    'date' => $dayDateTranslated,
                    'users' => array_values($dayUsers),
                    'posts' => array_values($dayPosts),
                    'topics' => array_values($dayTopics),
                ], JSON_UNESCAPED_UNICODE);

            break;

            case 'blog':
            
                $JSON = new \App\Model\File\JSON('http://api.phpcore.cz/novinky/');

                $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Index.json');
                $list->elm1('log')->show()->fill(data: $db->select('app.log.last()'));
                $list->elm1('news')->show()->fill(data: $JSON->get());
                $list->split(1, 1);
                $data->list = $list->getDataToGenerate();

            break;

            case 'static':
            
                $JSON = new \App\Model\File\JSON('http://api.phpcore.cz/novinky/');

                $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Index.json');
                $list->elm1('news')->show()->fill(data: $JSON->get());
                $list->split(1, 1);
                $data->list = $list->getDataToGenerate();

            break;

        }
    }
}