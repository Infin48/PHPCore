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

namespace Page\Admin;

use Block\Log;
use Block\User;
use Block\Chart;
use Block\Other;
use Block\Admin\Forum;

use Visualization\Lists\Lists;
use Visualization\Block\Block;
use Visualization\Field\Field;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Index
 */
class Index extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => 'Index',
        'permission' => 'admin.?'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('index')->active();

        // BLOCK
        $log = new Log();
        $user = new User();
        $forum = new Forum();
        $other = new Other();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();
        
        // FORUM STATS
        $stats = $forum->getStats();

        // BLOCK
        $block = new Block('Admin/Index');
        $block->object('user')->value($stats['user'])
            ->object('users')->value($user->getRecentCount())
            ->object('topic')->value($stats['topic'])
            ->object('post')->value($stats['post']);
        $this->data->block = $block->getData();

        // LIST
        $list = new Lists('Admin/Index');
        $list->object('log')->fill($log->getLast());

        foreach ($user->getRecent() as $user) {

            $list->object('users')->appTo($user)->jumpTo();

            if ($this->user->perm->has('admin.user') === false or $this->user->perm->compare(index: $user['group_index'], admin: $user['is_admin']) === false) {

                $list->delButton('edit');
            }
        }

        $news = json_decode(file_get_contents('http://api.phpcore.cz/novinky/'), true);
        $list->object('news')->fill($news);

        $this->data->list = $list->getData();

        // FIELD
        $field = new Field('Admin/Index');
        $field->disButtons();
        $field->object('info')
            ->row('version')->setValue($this->system->settings->get('site.version'))
            ->row('php')->setValue(phpversion())
            ->row('database')->setValue($other->version())
            ->row('started')->setValue($this->system->settings->get('site.started'));
        $this->data->field = $field->getData();

        // CHART BLOCK
        $chart = new Chart();

        $statsDay = $chart->getDay();

        // GENERATE ARRAY OF LAST 30 DAYS
        $dayDate = $dayDateTranslated = [];
        for ($i = 30; $i >= 0; $i--)
        {
            array_push($dayDate, date('Y-m-d', strtotime('-' . $i . ' days')));
            array_push($dayDateTranslated, mb_convert_case(strftime('%B %e, %Y', strtotime('-' . $i . ' days')), MB_CASE_TITLE));
        }

        $dayUsers = $dayPosts = $dayTopics = array_combine($dayDate, array_fill(0, count($dayDate), 0));
        
        // FILL DAYS
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

        $this->data->chart = json_encode([
            'date' => $dayDateTranslated,
            'users' => array_values($dayUsers),
            'posts' => array_values($dayPosts),
            'topics' => array_values($dayTopics),
        ], JSON_UNESCAPED_UNICODE);
    }
}