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

namespace App\Page\Admin\Report;

/**
 * Show
 */
class Show extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.forum';
    
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
            'run/report/close' => 'markReportedContentAsClosed',

            default => ''
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

        // Language
        $language = $data->get('inst.language');
        
        // If forum is not enabled
		if ($system->get('site_mode') != 'forum')
		{
            // Show error page
			$this->error404();
		}

        // Report data
        $row = $db->select('app.report.get()', $this->url->getID()) or $this->error404();

        // Save data about reported content
        $data->set('data.content', $row);

        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_REPORTED_TOPIC);
        $pagination->total($db->select('app.report-reason.countWithLog()', $this->url->getID()));
        $pagination->url($this->url->getURL());
        $data->pagination = $pagination->getData();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Admin/Report.json');
        $form
            ->form('report')
                ->callOnSuccess($this, 'markReportedContentAsClosed')
                ->disButtons();

        // Assign data based on type
        switch ($data->get('data.content.report_type'))
        {
            // Post
            case 'Post': 
                $type = 'post';
                $elm3 = 'post_id';
            break;

            // Topic
            case 'Topic': 
                $type = 'topic';
                $elm3 = 'topic_id';
            break;

            // Profile post
            case 'ProfilePost': 
                $type = 'profile-post';
                $elm3 = 'profile_post_id';
            break;

            // Comment under profile post
            case 'ProfilePostComment': 
                $type = 'profile-post-comment';
                $elm3 = 'profile_post_comment_id';
            break;
        }

        // Get data about reported content from database
        $content = $db->select('app.' . $type . '.get()', $data->get('data.content.report_type_id'), true);

        // Save content data
        $data->set('data.content', array_merge($data->get('data.content'), $content));

        // If deleted content doesn't exist
        if (empty($content))
        {
            redirect('/admin/report/');
        }

        $form
            // Fill form with all data
            ->data(array_merge($content, $data->get('data.content')))
            ->frame('show')
                // Show correct content ID
                ->elm3($elm3)
                    ->show()
                    ->set('data.value', $data->get('data.content.' . $elm3))
                // Url to reported content
                ->elm3('show')->set('data.href', '$' . $this->build->url->{lcfirst($data->get('data.content.report_type'))}($content));
            
        // Navbar
        $this->navbar->elm1('forum')->elm2('reported')->active()->elm3(strtolower($data->get('data.content.report_type')))->active();

        // Set default status language to closed
        $status = $language->get('L_REPORT.L_STATUS.L_CLOSED');

        // If report is not closed
        if ($data->get('data.content.report_status') == 0)
        {
            // Show button to close report
            $form->input('close')->show();

            // change status to pending
            $status = $language->get('L_REPORT.L_STATUS.L_PENDING');
        }

        // Save form and get ready to generate
        $data->form = $form->getDataToGenerate();

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Report/' . $data->get('data.content.report_type') . '.json');
        $breadcrumb->create()->jumpTo()->title($language->get('L_CONTENT_LIST.' . $data->get('data.content.report_type')))->href('/admin/report/show/' . $data->get('data.content.report_id'));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Block
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Report/Show.json');
        $block
            // Set type of content
            ->elm1('type')->value($language->get('L_CONTENT_LIST.' . $data->get('data.content.report_type')))
            // Set number of reports
            ->elm1('reasons')->value($db->select('app.report-reason.count()', $this->url->getID()))
            // Set date of first report
            ->elm1('first_report')->value($this->build->date->long($data->get('data.content.report_created')))
            // Set report status
            ->elm1('status')->value($status);

        // Save block and get ready to generate
        $data->block = $block->getDataToGenerate();

        // List
        $list = new \App\Visualization\ListsAdmin\ListsAdmin('Root/ListsAdmin:/Formats/Report/Show.json');

        // Fill list with all reports
        $list->elm1('reasons')->fill(data: $db->select('app.report-reason.all()', $this->url->getID()));

        // Save list and get ready to generate
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
    public function markReportedContentAsClosed( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Close report
        $db->query('
            UPDATE ' . TABLE_REPORTS . '
            SET report_status = 1
            WHERE report_id = ?
        ', [$data->get('data.content.report_id')]);
        
        // Add close information to report reasons
        $db->insert(TABLE_REPORTS_REASONS, [
            'user_id' => LOGGED_USER_ID,
            'report_id' => $data->get('data.content.report_id'),
            'report_reason_type' => (int)1
        ]);
        
        // Add record to log
        $db->addToLog( name: __FUNCTION__ );

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Refresh
        $data->set('options.refresh', true);
    }
}