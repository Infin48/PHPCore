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

use Block\User;
use Block\Chart;
use Block\Report;
use Block\Statistics as StatisticsBlock;
use Block\Admin\Forum;

use Visualization\Admin\Block\Block;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Statistics
 */
class Statistics extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Statistics',
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
        $this->navbar->object('other')->row('stats')->active();

        // BLOCK
        $user = new User();
        $stats = new StatisticsBlock();
        $forum = new Forum();
        $report = new Report();

        // BREADCRUMB
        $breadcrumb = new Breadcrumb('/Admin/Admin');
        $this->data->breadcrumb = $breadcrumb->getData();

        // FORUM STATS
        $statsForum = $forum->getStats();

        // REPORT STATS
        $statsReport = $report->getStats();

        // STATISTICS DATA
        $statistics = $stats->getAll();

        // BLOCK
        $block = new Block('/Statistics');
        $block->object('user')->value($statsForum['user'])
            ->object('users')->value($user->getRecentCount())
            ->object('user_deleted')->value($statistics['user_deleted'])
            ->object('topic')->value($statsForum['topic'])
            ->object('topic_reported')->value($statsReport['topic'])
            ->object('topic_deleted')->value($statistics['topic_deleted'])
            ->object('post')->value($statsForum['post'])
            ->object('post_reported')->value($statsReport['post'])
            ->object('post_deleted')->value($statistics['post_deleted']);
        $this->data->block = $block->getData();

        // CHART BLOCK
        $chart = new Chart();

        $statsMonth = $chart->getMonth();
        $statsDay = $chart->getDay();

        // GENERATE ARRAY OF LAST 30 DAYS
        $dayDate = $dayDateTranslated = [];
        for ($i = 30; $i >= 0; $i--) {

            array_push($dayDate, date('Y-m-d', strtotime('-' . $i . ' days')));
            array_push($dayDateTranslated, mb_convert_case(strftime('%B %e, %Y', strtotime('-' . $i . ' days')), MB_CASE_TITLE));
        }

        // GENERATE ARRAY OF LAST 12 MONTH
        $monthDate = $monthDateTranslated = [];
        for ($i = 12; $i >= 0; $i--) {

            array_push($monthDate, date('Y-m', strtotime('-' . $i . ' month')));
            array_push($monthDateTranslated, mb_convert_case(strftime('%B %Y', strtotime('-' . $i . ' month')), MB_CASE_TITLE));
        }

        $dayUsers = $dayPosts = $dayTopics = array_combine($dayDate, array_fill(0, count($dayDate), 0));
        $monthUsers = $monthPosts = $monthTopics = array_combine($monthDate, array_fill(0, count($monthDate), 0));
        
        foreach ($statsDay as $value) {

            foreach ($dayDate as $_date) {

                if ($value['day'] == $_date) {
                    $dayUsers[$_date] = $value['users'];
                    $dayPosts[$_date] = $value['posts'];
                    $dayTopics[$_date] = $value['topics'];
                    continue 2;
                }
            }
        }

        foreach ($statsMonth as $value) {

            foreach ($monthDate as $_date) {

                if ($value['month'] == $_date) {
                    $monthUsers[$_date] = $value['users'];
                    $monthPosts[$_date] = $value['posts'];
                    $monthTopics[$_date] = $value['topics'];
                    continue 2;
                }
            }
        }

        // CHART
        $this->data->chart = json_encode([
            'day' => [
                'date' => $dayDateTranslated,
                'users' => array_values($dayUsers),
                'posts' => array_values($dayPosts),
                'topics' => array_values($dayTopics)
            ],
            'month' => [
                'date' => $monthDateTranslated,
                'users' => array_values($monthUsers),
                'posts' => array_values($monthPosts),
                'topics' => array_values($monthTopics)
            ]
        ], JSON_UNESCAPED_UNICODE);    
    }
}