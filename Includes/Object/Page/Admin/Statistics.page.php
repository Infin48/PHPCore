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
 * Statistics
 */
class Statistics extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Statistics.phtml';

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
        
        // If forum is not enabled
		if ($system->get('site.mode') != 'forum')
		{
            // Show 404 error page
			$this->error404();
		}
        
        // Navbar
        $this->navbar->elm1('other')->elm2('stats')->active();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Statistics.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Save forum stats and unite with others
        $data->set('data.forum-stats', $db->select('app.forum.stats()'));

        // Save report stats and unite with others
        $data->set('data.report-stats', $db->select('app.report.stats()'));

        // Save other stats and unite with others
        $data->set('data.deleted-stats', $db->select('app.deleted.stats()'));

        // Block
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Statistics.json');

        $block
            // Set number of registered users
            ->elm1('user')->value($data->get('data.forum-stats.user'))
            // Set number of recent registered users
            ->elm1('users')->value($db->select('app.user.recentCount()'))
            // Set number of deleted users
            ->elm1('user_deleted')->value($data->get('data.deleted-stats.user_deleted'))
            // Set number of created topics
            ->elm1('topic')->value($data->get('data.forum-stats.topic'))
            // Set number of reported topics
            ->elm1('topic_reported')->value($data->get('data.report-stats.topic'))
            // Set number of deleted topics
            ->elm1('topic_deleted')->value($data->get('data.deleted-stats.topic_deleted'))
            // Set number of created posts
            ->elm1('post')->value($data->get('data.forum-stats.post'))
            // Set number of reported posts
            ->elm1('post_reported')->value($data->get('data.report-stats.post'))
            // Set number of deleted posts
            ->elm1('post_deleted')->value($data->get('data.deleted-stats.post_deleted'));

        // Split block
        $block->split(3, 3, 3);

        // Save block and get ready to generate
        $data->block = $block->getDataToGenerate();

        $statsMonth = $db->select('app.chart.month()');
        $statsDay = $db->select('app.chart.day()');

        // Generate array of last 30 days
        $dayDate = $dayDateTranslated = [];
        for ($i = 30; $i >= 0; $i--)
        {
            $dayDate[] =  date('Y-m-d', strtotime('-' . $i . ' days'));
            $dayDateTranslated[] = mb_convert_case(strftime('%B %e, %Y', strtotime('-' . $i . ' days')), MB_CASE_TITLE);
        }

        // Generate array of last 12 month
        $monthDate = $monthDateTranslated = [];
        for ($i = 11; $i >= 0; $i--)
        {
            $monthDate[] = $date = date('Y-m', strtotime(date('Y-m-01') . '-' . $i . ' months'));
            $monthDateTranslated[] = mb_convert_case(strftime('%B %Y', strtotime($date)), MB_CASE_TITLE);
        }

        $dayUsers = $dayPosts = $dayTopics = array_combine($dayDate, array_fill(0, count($dayDate), 0));
        $monthUsers = $monthPosts = $monthTopics = array_combine($monthDate, array_fill(0, count($monthDate), 0));

        foreach ($statsDay as $value)
        {
            foreach ($dayDate as $_date)
            {
                if ($value['day'] == $_date)
                {
                    $dayUsers[$_date] = $value['users'];
                    $dayPosts[$_date] = $value['posts'];
                    $dayTopics[$_date] = $value['topics'];
                    continue 2;
                }
            }
        }
        
        foreach ($statsMonth as $value)
        {
            foreach ($monthDate as $_date)
            {
                if ($value['month'] == $_date)
                {
                    $monthUsers[$_date] = $value['users'];
                    $monthPosts[$_date] = $value['posts'];
                    $monthTopics[$_date] = $value['topics'];
                    continue 2;
                }
            }
        }
        
        // Chart
        $data->chart = json_encode([
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